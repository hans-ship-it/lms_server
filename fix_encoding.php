<?php
$dir = new RecursiveDirectoryIterator(__DIR__);
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/.*\.php$/', RegexIterator::GET_MATCH);

$replacements = [
    'â€”' => '&mdash;',
    'â€º' => '&rsaquo;',
    'â€¢' => '&bull;',
    'â€“' => '&ndash;',
    'â‰¥' => '&ge;'
];

$count = 0;
foreach($files as $file) {
    $path = $file[0];
    if (strpos($path, 'fix_encoding.php') !== false) continue;
    
    $content = file_get_contents($path);
    $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);
    
    if ($content !== $newContent) {
        file_put_contents($path, $newContent);
        echo "Fixed: $path\n";
        $count++;
    }
}
echo "Total files fixed: $count\n";
?>
