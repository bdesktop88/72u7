<?php
if (isset($_GET['folder'])) {
    $folder = urldecode($_GET['folder']);  // Get the folder path from the URL

    if (is_dir($folder)) {
        // Recursively delete the folder and its contents
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        rmdir($folder);  // Remove the empty folder
        echo "Folder and files deleted successfully.";
    } else {
        echo "Folder not found.";
    }
}
?>

<button onclick="history.back(); history.back();">Go Back</button>