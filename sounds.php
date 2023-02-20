<?php

set_time_limit(0);

$base_url  = 'https://store.ambianceapp.com/?page=%d';
$sound_url = 'https://store.ambianceapp.com/products/%s/';

$path = "saved/";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$sounds = array();
$total  = 0;

foreach (range(1, 169) as $p) {
    curl_setopt($ch, CURLOPT_URL, sprintf($base_url, $p, $p));
    $s = curl_exec($ch);
    
    $matches = array();
    
    preg_match_all('/<a itemprop="url" href="\/products\/([^\.]+)">/', $s, $matches);
    
    echo 'crawling page ' . $p . "\n";
    
    $ii = 0;
    
    foreach ($matches[1] as $ps) {
        
        curl_setopt($ch, CURLOPT_URL, sprintf($sound_url, $ps));
        $s = curl_exec($ch);
        
        preg_match('/<source src="([^\"]+)">/', $s, $matches2);

        $sound = sprintf($sound_url, $ps);
        $sound_file = preg_replace('/\?.*/', '', $matches2[1]);
        $sounds[]   = sprintf('"%s","%s","%s.mp3","%s"', $sound, $ps, $ps, $sound_file);
        $saved      = sprintf('%s/%s.mp3', $path, $ps);
        
        $fp = fopen($saved, 'w+');
        
        $fetch = curl_init($sound_file);

        curl_setopt($fetch, CURLOPT_TIMEOUT, 50);
        curl_setopt($fetch, CURLOPT_FILE, $fp);
        curl_setopt($fetch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($fetch);
        curl_close($fetch);
        fclose($fp);
        
        echo 'saving ' . $ps . '.mp3' . "\n";
        
        $ii++;
        $total++;
    }
    echo $ii . ' sounds found on page ' . $p . "\n";
    flush();
}

echo $total . ' total sounds found and saved.' . "\n";

sort($sounds);

$fp = fopen('./sounds.csv', 'w');
fwrite($fp, implode("\n", $sounds));
fclose($fp);

