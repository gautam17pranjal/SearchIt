<?php 
    ini_set('memory_limit', -1);
?>
<?php 
    // auto correction file
    include 'SpellCorrector.php';
    // echo SpellCorrector::correct('electon');         //--> how to show the corrected word
?>

<?php
    $limit = 10;
    $query = isset($_REQUEST['q']) ? $_REQUEST['q']: false;
    $results = false;
    $engine_name = "SearchIt!!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv='cache-control' content='no-cache'>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <!-- <script src="http://code.jquery.com/jquery-1.11.0.min.js"></script> -->
    <link rel="stylesheet" href="style.css">
    <!-- <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
    for auto-complete
    <link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet">
    <script src="script.js"></script>
    <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script> -->
    
	<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>

    <link href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet">
    <script src="script.js"></script>
    <script src="http://code.jquery.com/ui/1.10.2/jquery-ui.js" ></script>

    <title>Search!</title>
</head>
<body>
<?php
    if(!$query){
        ?>
        <div class="contianer-fluid">
            <div class="row">
                <div class="col-12 content">
                    <div class="name">
                        <?php echo $engine_name;?>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="search_box">
                        <form accept-charset="utf-8">
                            <input type="text" placeholder="Search..." id="q" name="q" value="<?php echo htmlspecialchars($query, ENT_QUOTES, "utf-8");?>">
                            <br><br>
                            <div class="radio">
                                <input type="radio" id="rad_lucene" name="algorithm" value="lucene" /> Solr's Lucene
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="radio" id="rad_page_rank" name="algorithm" value="pagerank" /> PageRank
                            </div>
                            <input type="submit" value="Search" id="submit">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    else{   // query variable is set; so show the results
        require_once('solr-php-client-master/Apache/Solr/Service.php');
        $solr = new Apache_Solr_Service('localhost', 8983, '/solr/ny_times_core/');
        if(get_magic_quotes_gpc() == 1){
            $query = stripslashes($query);
        }
        // spell correction
        $query_terms = explode(" ", $query);
        $correct_terms = [];
        foreach ($query_terms as $term)
            $correct_terms[] = SpellCorrector::correct($term);
        echo "<script>console.log('" . array_values($correct_terms)[0] . "')</script>";
        $correct_query = implode(" ", $correct_terms);
        if (strtolower($query) != strtolower($correct_query))
            $spellCheck = true;
        try{
            if(!isset($_GET['algorithm'])){
                $algo = "lucene";
                $_GET['algorithm'] = "lucene";
            }
            if($_GET['algorithm'] == "lucene"){
                $results = $solr->search($query, 0, $limit);
                $algo = "lucene";
            }
            else{
                $param = array('sort'=>'pageRankFile desc');
                $results = $solr->search($query, 0, $limit, $param);
                $algo = "pagerank";
            }
        }
        catch (Exception $e){
            die("<html><head><title>SEARCH EXCEPTION</title><body><pre>{$e->__toString()}</pre></body></html>");
        }
        if($results){
            $total = (int)$results->response->numFound;
            $start = min(1, $total);
            $end = min($limit, $total);
            ?>
            <!-- show results -->
            <div class="container-fluid">
                <div class="row results-head">
                    <div class="col-md-4 hidden-sm">
                        <div class="results-title">
                            <a href="index.php"><?php echo $engine_name;?></a>
                        </div>
                    </div>
                    <div class="col-md-8 col-12 results-query">
                        Search Query: <?php echo $query;?>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Algorithm: <?php echo $_GET['algorithm'];?><br>
                        <div class="spell-check">
                            <?php
                                if(isset($spellCheck) and $spellCheck){
                                    echo "Showing results for ", $query;
                                    $link = "?q=$correct_query&algorithm=$algo";
                                    echo "<br>Did you mean <a href='$link'>$correct_query</a>?";
                                }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="results-summary">
                            Showing <?php echo $end ?> out of <?php echo $total ?> results
                        </div>
                        <div class="d-flex flex-column bd-highlight mb-3 results-block">
                            <?php
                                foreach($results->response->docs as $doc){
                                    $title = $doc->title;
                                    $url = $doc->og_url;
                                    $id = $doc->id;
                                    $description = $doc->og_description;
                                    if($title == "" || $title == null)
                                        $title = "N/A";
                                    $nytimes_csv = array_map('str_getcsv', file('URLtoHTML_nytimes_news.csv'));  
                                    if($url == "" || $url == null){
                                        foreach($nytimes_csv as $record){
                                            $temp = "/Users/gautampranjal/Desktop/solr-7.7.3/server/solr/NYTIMES/nytimes/".$record[0];
                                            if ($id == $temp) {
                                                $url = $record[1];
                                                unset($record);
                                                break;
                                            }
                                        }
                                    }
                                    if($description == "" || $description == null)
                                        $description = "N/A";  
                                    // now display the result for that record
                                    ?>
                                    <div class="bd-highlight results-record">
                                        <a href="<?php echo $url;?>" id="results-urls" target="_blank"><?php echo $url."<br>";?></a>
                                        <a href="<?php echo $url;?>" id="results-title" target="_blank"><?php echo $title."<br>";?></a>
                                        <span id="results-id"><?php echo $id."<br>";?></span>
                                        <span id="results-description"><?php echo $description."<br>";?></span>
                                    </div>
                                    <?php
                                }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>
    
    <!-- <script src="https://code.jquery.com/jquery-3.1.1.min.js"> -->
    <!-- // <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script> -->
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.min.js" integrity="sha384-VHvPCCyXqtD5DqJeNxl2dtTyhF78xXNXdkwX1CZeRusQfRKp+tA7hAShOK/B/fQ2" crossorigin="anonymous"></script> -->
</body>
</html>