<?php
require_once(__DIR__ . '/password.php');

class AutoUpdater {
    private const GITHUB_REPO = 'dvygolov/YellowCloaker';
    private const GITHUB_BRANCH = 'multipleconfigs';
    private const GITHUB_API_URL = 'https://api.github.com/repos/dvygolov/YellowCloaker/contents/admin/version.txt?ref=multipleconfigs';
    private const VERSION_FILE = __DIR__ . '/version.txt';
    private const SETTINGS_FILE = __DIR__ . '/../settings.php';
    private const BACKUP_DIR = __DIR__ . '/../backups';
    private const UPDATE_DIR = __DIR__ . '/../temp_update';

    private $currentVersion;
    private $latestVersion;
    private $downloadUrl;

    public function __construct() {
        $this->currentVersion = trim(file_get_contents(self::VERSION_FILE));
    }

    public function checkForUpdates(): bool {
        try {
            $opts = [
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: PHP',
                        'Accept: application/vnd.github.v3+json'
                    ]
                ]
            ];
            $context = stream_context_create($opts);
            $response = file_get_contents(self::GITHUB_API_URL, false, $context);
            
            if ($response === false) {
                throw new Exception("Failed to fetch version information");
            }

            $fileInfo = json_decode($response, true);
            if (!$fileInfo || !isset($fileInfo['content'])) {
                throw new Exception("Invalid version file information");
            }

            $this->latestVersion = trim(base64_decode($fileInfo['content']));

            $latestTimestamp = $this->convertVersionToTimestamp($this->latestVersion);
            $currentTimestamp = $this->convertVersionToTimestamp($this->currentVersion);

            return $latestTimestamp > $currentTimestamp;
        } catch (Exception $e) {
            error_log("Update check failed: " . $e->getMessage());
            return false;
        }
    }

    private function convertVersionToTimestamp(string $version): int {
        $parts = explode('.', $version);
        if (count($parts) !== 3) {
            throw new Exception("Invalid version format");
        }
        return mktime(0, 0, 0, $parts[1], $parts[0], 2000 + intval($parts[2]));
    }

    public function update(): array {
        $result = ['success' => false, 'message' => ''];

        try {
            // Create necessary directories
            if (!file_exists(self::BACKUP_DIR)) {
                mkdir(self::BACKUP_DIR, 0755, true);
            }
            if (!file_exists(self::UPDATE_DIR)) {
                mkdir(self::UPDATE_DIR, 0755, true);
            }

            // Backup settings.php
            $settingsBackup = self::BACKUP_DIR . '/settings_' . date('Y-m-d_H-i-s') . '.php';
            if (!copy(self::SETTINGS_FILE, $settingsBackup)) {
                throw new Exception("Failed to backup settings.php");
            }

            // Download and extract update
            $zipFile = self::UPDATE_DIR . '/update.zip';

            $this->downloadUrl = "https://api.github.com/repos/" . self::GITHUB_REPO . "/zipball/" . self::GITHUB_BRANCH;
            if (!$this->downloadFile($this->downloadUrl, $zipFile)) {
                throw new Exception("Failed to download update");
            }

            $zip = new ZipArchive();
            if ($zip->open($zipFile) !== true) {
                throw new Exception("Failed to open update archive");
            }

            // Extract to temporary directory
            $zip->extractTo(self::UPDATE_DIR);
            $zip->close();

            // Find the extracted directory (it will be named like owner-repo-hash)
            $extractedDir = glob(self::UPDATE_DIR . '/*', GLOB_ONLYDIR)[0];
            if (!$extractedDir) {
                throw new Exception("Failed to locate extracted files");
            }

            //TODO: uncomment when ready
            // Copy files recursively, excluding settings.php
            // $this->recursiveCopy($extractedDir, dirname(__DIR__), ['settings.php']);

            // Clean up
            $this->recursiveDelete(self::UPDATE_DIR);

            $result['success'] = true;
            $result['message'] = "Successfully updated to version " . $this->latestVersion;
        } catch (Exception $e) {
            $result['message'] = "Update failed: " . $e->getMessage();
            // Restore settings if needed
            if (isset($settingsBackup) && file_exists($settingsBackup)) {
                copy($settingsBackup, self::SETTINGS_FILE);
            }
        }

        return $result;
    }

    private function downloadFile(string $url, string $path): bool {
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => [
                    'User-Agent: PHP',
                    'Accept: application/vnd.github.v3+json'
                ]
            ]
        ];
        $context = stream_context_create($opts);
        $content = file_get_contents($url, false, $context);
        return $content !== false && file_put_contents($path, $content) !== false;
    }

    private function recursiveCopy(string $src, string $dst, array $excludeFiles = []): void {
        $dir = opendir($src);
        if (!file_exists($dst)) {
            mkdir($dst);
        }
        
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') continue;
            if (in_array($file, $excludeFiles)) continue;

            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;

            if (is_dir($srcPath)) {
                $this->recursiveCopy($srcPath, $dstPath, $excludeFiles);
            } else {
                copy($srcPath, $dstPath);
            }
        }
        closedir($dir);
    }

    private function recursiveDelete(string $dir): void {
        if (!file_exists($dir)) return;

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function getCurrentVersion(): string {
        return $this->currentVersion;
    }

    public function getLatestVersion(): string {
        return $this->latestVersion;
    }
}

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!check_password(false)){
        $response = ['success' => false, 'message' => 'Incorrect password'];
    }
    else {
        $updater = new AutoUpdater();
        $response = ['success' => false, 'message' => ''];

        switch ($_POST['action']) {
            case 'check':
                $hasUpdate = $updater->checkForUpdates();
                $response = [
                    'success' => true,
                    'hasUpdate' => $hasUpdate,
                    'version' => $hasUpdate ? $updater->getLatestVersion() : $updater->getCurrentVersion()
                ];
                break;

            case 'update':
                $result = $updater->update();
                $response = [
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
                break;

            default:
                $response['message'] = 'Invalid action';
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    http_response_code(405);
    echo 'Method Not Allowed';
}
?>