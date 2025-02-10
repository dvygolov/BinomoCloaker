<div id="version">
    <div class="version-grid">
        <div class="version-item">
            <span class="version-label">Version</span>
            <span class="version-value"><?= file_get_contents(__DIR__.'/version.txt') ?></span>
        </div>
        <div class="version-item">
            <span class="version-label">PHP</span>
            <span class="version-value"><?= phpversion() ?></span>
        </div>
        <div class="version-item">
            <span class="version-label">SQLite</span>
            <span class="version-value"><?= extension_loaded('sqlite3') ? SQLite3::version()['versionString'] : '<span class="error">Not Found!</span>' ?></span>
        </div>
        <div class="version-item">
            <span class="version-label">cURL</span>
            <span class="version-value"><?= extension_loaded('curl') ? curl_version()['version'] : '<span class="error">Not Found!</span>' ?></span>
        </div>
        <div class="version-item">
            <span class="version-label">Zip</span>
            <span class="version-value"><?= extension_loaded('zip') ? 'Found' : '<span class="error">Not Found, autoupdate will fail!</span>' ?></span>
        </div>
    </div>
</div>