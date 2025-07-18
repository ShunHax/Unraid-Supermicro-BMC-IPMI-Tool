<?php
/**
 * Simple XML validation for the .plg file
 */

$plg_file = 'supermicro-ipmi.plg';

echo "Validating XML syntax for: $plg_file\n\n";

// Read the file
$xml_content = file_get_contents($plg_file);

if ($xml_content === false) {
    echo "ERROR: Could not read file $plg_file\n";
    exit(1);
}

// Enable error reporting for XML parsing
libxml_use_internal_errors(true);

// Try to parse the XML
$xml = simplexml_load_string($xml_content);

if ($xml === false) {
    echo "ERROR: XML parsing failed!\n\n";
    echo "XML Errors:\n";
    foreach (libxml_get_errors() as $error) {
        echo "  Line {$error->line}: {$error->message}\n";
    }
    libxml_clear_errors();
    exit(1);
} else {
    echo "SUCCESS: XML is valid!\n\n";
    
    // Display basic plugin info
    echo "Plugin Information:\n";
    echo "  Name: " . $xml['name'] . "\n";
    echo "  Author: " . $xml['author'] . "\n";
    echo "  Version: " . $xml['version'] . "\n";
    echo "  Launch: " . $xml['launch'] . "\n";
    echo "  Min Version: " . $xml['min'] . "\n";
    echo "  Max Version: " . $xml['max'] . "\n";
    
    // Count files
    $file_count = count($xml->FILE);
    echo "  Files: $file_count\n";
    
    echo "\nXML validation completed successfully!\n";
}
?> 