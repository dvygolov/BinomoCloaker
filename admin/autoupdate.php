<?php
require_once('auth.php');

class AutoUpdater {
    private const GITHUB_REPO = 'dvygolov/YellowCloaker';
    private const GITHUB_API_URL = 'https://api.github.com/repos/dvygolov/YellowCloaker/releases/latest';
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
                throw new Exception("Failed to fetch release information");
            }

            $releaseInfo = json_decode($response, true);
            if (!$releaseInfo || !isset($releaseInfo['tag_name'])) {
                throw new Exception("Invalid release information");
            }

            $this->latestVersion = trim($releaseInfo['tag_name'], 'v');
            $this->downloadUrl = $releaseInfo['zipball_url'];

            return version_compare($this->latestVersion, $this->currentVersion, '>');
        } catch (Exception $e) {
            error_log("Update check failed: " . $e->getMessage());
            return false;
        }
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

            // Copy files recursively, excluding settings.php
            $this->recursiveCopy($extractedDir, dirname(__DIR__), ['settings.php']);

            // Update version file
            file_put_contents(self::VERSION_FILE, $this->latestVersion);

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
    $updater = new AutoUpdater();
    $response = ['success' => false, 'message' => ''];

    switch ($_POST['action']) {
        case 'check':
            $hasUpdate = $updater->checkForUpdates();
            $response = [
                'success' => true,
                'hasUpdate' => $hasUpdate,
                'currentVersion' => $updater->getCurrentVersion(),
                'latestVersion' => $updater->getLatestVersion()
            ];
            break;

        case 'update':
            $response = $updater->update();
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Yellow Cloaker Auto Update</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .status { margin: 20px 0; padding: 15px; border-radius: 4px; }
        .status.success { background-color: #d4edda; color: #155724; }
        .status.error { background-color: #f8d7da; color: #721c24; }
        .status.info { background-color: #cce5ff; color: #004085; }
        button { padding: 10px 20px; margin: 10px 0; cursor: pointer; }
        .hidden { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Yellow Cloaker Auto Update</h1>
        <div id="status" class="status info">
            Checking for updates...
        </div>
        <button id="checkBtn" onclick="checkForUpdates()">Check for Updates</button>
        <button id="updateBtn" onclick="performUpdate()" class="hidden">Update Now</button>
    </div>

    <script>
        function checkForUpdates() {
            fetch('autoupdate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=check'
            })
            .then(response => response.json())
            .then(data => {
                const status = document.getElementById('status');
                const updateBtn = document.getElementById('updateBtn');
                
                if (data.success) {
                    if (data.hasUpdate) {
                        status.className = 'status info';
                        status.innerHTML = `Update available! Current version: ${data.currentVersion}, Latest version: ${data.latestVersion}`;
                        updateBtn.classList.remove('hidden');
                    } else {
                        status.className = 'status success';
                        status.innerHTML = 'You have the latest version installed.';
                        updateBtn.classList.add('hidden');
                    }
                } else {
                    status.className = 'status error';
                    status.innerHTML = 'Failed to check for updates.';
                    updateBtn.classList.add('hidden');
                }
            })
            .catch(error => {
                const status = document.getElementById('status');
                status.className = 'status error';
                status.innerHTML = 'Error checking for updates: ' + error;
                document.getElementById('updateBtn').classList.add('hidden');
            });
        }

        function performUpdate() {
            const status = document.getElementById('status');
            const updateBtn = document.getElementById('updateBtn');
            
            status.className = 'status info';
            status.innerHTML = 'Updating...';
            updateBtn.disabled = true;

            fetch('autoupdate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=update'
            })
            .then(response => response.json())
            .then(data => {
                status.className = data.success ? 'status success' : 'status error';
                status.innerHTML = data.message;
                updateBtn.disabled = false;
                if (data.success) {
                    updateBtn.classList.add('hidden');
                }
            })
            .catch(error => {
                status.className = 'status error';
                status.innerHTML = 'Error during update: ' + error;
                updateBtn.disabled = false;
            });
        }

        // Check for updates when page loads
        window.onload = checkForUpdates;
    </script>
</body>
</html>
