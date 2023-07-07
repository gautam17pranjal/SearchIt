<?php
    $query = $_GET['query'];
    $url = "http://localhost:8983/solr/ny_times_core/suggest?q=".$query;
    $results = file_get_contents($url);
    $res = json_decode($results);
    $response = $res->suggest->suggest->$query;
    // $suggestions = $res->suggest->suggest->$query->suggestions;
    // $response->num = $num_res;
    // $response->suggestions = $suggestions;
    $myResponse = json_encode($response);
    echo $myResponse;
?>
