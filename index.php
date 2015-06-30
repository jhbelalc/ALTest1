<?php

/* 
 * John Harold Belalcazar Lozano
 * search on themoviedb.org with api json
 * 
 */
$tableactors ="";
$tablemovies ="";
$tablemovied ="";
$actorbi = "";

//to persist the search type 1 day
if(!isset($_COOKIE['selsearch'])) {
    setcookie('selsearch', '1', time() + (86400 * 30), "/");
    $_COOKIE['selsearch'] = '1';
} else {
    setcookie('selsearch', $_POST['selectsearch'], time() + (86400 * 30), "/");
}
//rest of the cookie values handled in scripts.js
$selsearch= $_COOKIE['selsearch'];

date_default_timezone_set('America/New_York'); 
//BIO from Actor
function wikidefinition($s) {
    $json=file_get_contents("http://en.wikipedia.org/w/api.php?action=opensearch&search=".$s);
    $bio = json_decode($json);
    if ((string)$bio[0]){
        return $bio;
    }
    else
    {
        return array("No Bio Found","Searched on Wikipedia");
    }  
}

//Table with list of Actors
function listActors($actnam){
    $json = file_get_contents('http://api.themoviedb.org/3/search/person?query=&api_key=22e8595de8c06be02009d7efb076399e&query='. $actnam);
    $actors = json_decode($json);
    
    $tableactors="<table width='50%' align='left' class='table table-hover result'>";
    $tableactors.="<tr><th>Id Actor</th><th>Name</th><th>Photo</th><th>Known For</th></tr>";

    foreach ($actors->results as $actor) {
        $tableactors.="<tr><td>";
        $tableactors.=$actor->id;
        $tableactors.="</td><td>";
        $tableactors.="<a href='index.php?id=".base64_encode($actor->id)."&nm=".  base64_encode($actor->name)."&pp=".base64_encode($actor->profile_path)."'>".$actor->name."</a>";
        $tableactors.="</td><td>";
        if ($actor->profile_path!=""){
            $srcimg='https://image.tmdb.org/t/p/w92/'.$actor->profile_path;
        }
        else{
            $srcimg='images/no-profile-w92.jpg';
        }
        $tableactors.="<img src='".$srcimg."' alt='' class='img-rounded'/></td><td>";
        $movies="";
        foreach($actor->known_for as $known){
            $movies.= (strlen($movies)>0 ? ', ' : '') . $known->original_title;
        }
        $tableactors.=$movies."</td></tr>";
        //$actorbi = wikidefinition($actnam);
    }
    $tableactors.="</table>";
    return $tableactors;
}

//Table with List of Movies
function listMovies(){
    $actorid = base64_decode($_GET['id']);
    $actornm = base64_decode($_GET['nm']);
    $actorpi = base64_decode($_GET['pp']);
    if ($actorid=="" || $actornm==""){
        echo $actorid;
        echo $actornm;
        die();
    }
    $json = \file_get_contents('http://api.themoviedb.org/3/person/' . $actorid . '/movie_credits?query=&api_key=22e8595de8c06be02009d7efb076399e');
    $movies = json_decode($json);
    usort($movies->cast, function($a, $b) { return strtotime($b->release_date) - strtotime($a->release_date); });
    $actorbi = wikidefinition(str_replace(" ","+",$actornm));
    $tablemovies="";
    $tablemovies="<table><tr><td>";
    if ($actorpi!=""){
        $srcimg='https://image.tmdb.org/t/p/w92/'.$actorpi;
    }
    else{
        $srcimg='images/no-profile-w92.jpg';
    }
    $tablemovies.="<img src='".$srcimg."' alt='' class='img-rounded'/></td><td>&nbsp;</td><td>";
    $tablemovies.="<table><tr><td>".$actorbi[0]."</td></tr>";
    $tablemovies.="<tr><td>".$actorbi[2][0]."</td></tr>";
    $tablemovies.="<tr><td>Extracted from: <a href='".$actorbi[3][0]."' target='_blank' >".$actorbi[3][0]."</a></td></tr>";
    $tablemovies.="<tr><td>&nbsp;</td></tr>";
    $tablemovies.="</table></td></tr></table>";
    $tablemovies.="</div>";
    $tablemovies.="<table width='50%' align='center' class='table table-hover result'>";
    $tablemovies.="<tr><td width='20%'>Date</td><td width='40%'>Movie</td><td width='40%'>Poster</td></tr>";
    foreach($movies->cast as $movie){
        $tablemovies.="<tr><td>".$movie->release_date."</td>";
        $tablemovies.="<td><a href='index.php?mid=".base64_encode($movie->id)."&nm=".base64_encode($actornm)."&pi=".base64_encode($actorpi)."'>".$movie->title ."</a>"; 
        if ($movie->poster_path!=''){
            $srcimage="https://image.tmdb.org/t/p/w92/".$movie->poster_path;
        }else{
            $srcimage="images/no_movie_poster.jpg";
        }
        $tablemovies.="</td><td><img src='".$srcimage."' alt='' class='img-rounded'/>";
        $tablemovies.="</td></tr>";
    }
    
    $tablemovies.="</table>";
    $tablemovies=str_replace('"','´',$tablemovies);
    return $tablemovies;
}

//Table of Movies by Name of the movie
function mb_listMovies($strMovies){
    $json = file_get_contents('http://api.themoviedb.org/3/search/movie?query='.$strMovies.'&api_key=22e8595de8c06be02009d7efb076399e');
    $tablemovies="<table  width='50%' align='center' class='table table-hover result'><tr><th>Date</th><th>Movie</th><th>Overview</th><th>Poster</th></tr>";
    $movies = json_decode($json);
    usort($movies->results, function($a, $b) { return strtotime($b->release_date) - strtotime($a->release_date); });
    
    foreach($movies->results as $video){
        if ($video->poster_path!=""){
            $srcimage="https://image.tmdb.org/t/p/w92/".$video->poster_path;
        } else {
            $srcimage="images/no_movie_poster.jpg";
        }
        $tablemovies.="<tr><td>".$video->release_date."</td>";
        $tablemovies.="<td><a href='index.php?mid=".base64_encode($video->id)."&nm=".base64_encode($video->original_title)."&pi=".base64_encode("none")."'>".$video->original_title ."</a></td>";
        $tablemovies.="<td>".$video->overview."</td><td><img src='".$srcimage."' alt='' class='img-rounded'/></td></tr>" ;
        //$tablemovies.="<a href='index.php?mid=".base64_encode($video->id)."&nm=".base64_encode($video->original_title)."&pi=".base64_encode("none")."'>".$video->original_title ."</a>";
        //$tablemovies-="</td><td>".$video->overview."</td><td><img src='".$srcimage."' alt='' class='img-rounded'/></td></tr>";
    }
    $tablemovies.="</table>";
    $tablemovies=str_replace('"','´',$tablemovies);
    return $tablemovies;
}

//Table with Movie Detail
function detailMovie(){
    $movieid = base64_decode($_GET['mid']);
    $actornm = base64_decode($_GET['nm']);
    $actorpi = base64_decode($_GET['pi']);
    if ($movieid=="" || $actornm==""){
        die();
    } 
    $json = file_get_contents('http://api.themoviedb.org/3/movie/' . $movieid . '?query=&api_key=22e8595de8c06be02009d7efb076399e');
    $movie = json_decode($json);
    //Get the trailers-videos for the movie
    $json = file_get_contents('http://api.themoviedb.org/3/movie/'. $movie->id . '/videos?query=&api_key=22e8595de8c06be02009d7efb076399e');
    $vidmovie = json_decode($json);
    $vidlinks = "";
    foreach($vidmovie->results as $video){
        $vidlinks.=(strlen($vidlinks)>0 ? ', ' : '') . "<a href='http://www.youtube.com/watch?v=" . $video->key . "' class='fancybox-media' > " . $video->name . "</a>" ;
    }
    $actorbi = wikidefinition(str_replace(" ","+",$actornm));
 
    $tablemovied="";

    $tablemovied.="<table><tr><td>";
    if ($actorpi!=""){
        $srcimg='https://image.tmdb.org/t/p/w92/'.$actorpi;
    }
    else{
        $srcimg='images/no-profile-w92.jpg';
    }
    $tablemovied.="<img src='".$srcimg."' alt='' class='img-rounded'/>"; 
    $tablemovied.="</td><td>&nbsp;</td><td>";
    $tablemovied.="<table><tr><td>".$actorbi[0]."</td></tr>";
    $tablemovied.="<tr><td>".$actorbi[2][0]."</td></tr>";
    $tablemovied.="<tr><td><a href='".$actorbi[3][0]."' target='_blank' >".$actorbi[3][0]."</a>";
    $tablemovied.="</td></tr></table></td></tr></table></div>";            

    $tablemovied.="<table width='80%' align='center' class='table table-hover result'>";
    $tablemovied.="<tr><td width='15%'>Poster</td><td width='15%'>Title</td><td width='40%'>Overview</td><td width='30%'>Videos</td></tr>";
    if ($movie->poster_path!=''){
        $srcimage="https://image.tmdb.org/t/p/w92/".$movie->poster_path;
    }else{
        $srcimage="images/no_movie_poster.jpg";
    }
    
    $tablemovied.="<tr><td><img src='".$srcimage."' /></td>";
    $tablemovied.="<td>".$movie->original_title."</td>";
    $tablemovied.="<td>".$movie->overview."</td>";
    $tablemovied.="<td>".$vidlinks."</td></tr></table>";
    $tablemovied=str_replace('"', '´', $tablemovied);
    return $tablemovied;
}

if ($_POST["btnsub"]){
    $actnam=$_POST['txtsrch'];
    $actnam= str_replace(" ","+",$actnam);
    $actorbi = "1";
    if ($selsearch!="2"){
        $tableactors=listActors($actnam);
    } else{
        $tablemovies=  mb_listMovies($actnam);
        $actorbi="2";
    }
} else{
    if ($_GET['id']){
        $actorid = base64_decode($_GET['id']);
        if ($actorid.length>0){
            $tablemovies=listMovies();
            $actorbi="2";
            //header('location:index.php');
        }            
    } else{
        if ($_GET['mid']){
            $movieid = base64_decode($_GET['mid']);
            if ($movieid.length>0){
                $tablemovied=detailMovie();
                $actorbi="3";
                //die(print_r($tablemovied));
                //header('location:index.php');
            }            
        }
    }
}

?>
<html>
    <head>
        <meta charset="utf-8">        
        <link href="css/Bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css"/>        
        <link href="css/typeahead.css" rel="stylesheet" type="text/css"/>
        <link href="js/fancyBox/source/jquery.fancybox.css" rel="stylesheet" type="text/css"/>
        <link href="js/fancyBox/source/helpers/jquery.fancybox-thumbs.css" rel="stylesheet" type="text/css"/>
        <link href="js/fancyBox/source/helpers/jquery.fancybox-buttons.css" rel="stylesheet" type="text/css"/>        
        <title>Search by Actor's Name</title>
    </head>
    <body>
        <div class="navbar navbar-inverse" role="navigation">
            <form class="navbar-form" role="search" action="" method="post" >
		<div class="input-group">
                    <table>
                        <tr>
                            <td width='10%'>
                                <a href='index.php'  >
                                    <img src="images/var_8_0_tmdb-logo-2_Bree.png" alt="" title="Return to home" width='80'/>                                    
                                </a>
                            </td>
                            <td>
                                <div class="dropdown" >
                                    <label style="font-size: small;  color: whitesmoke">Search by:
                                        <select id="selectsearch" name="selectsearch" style="font-size: large; color: black" onchange="asignSearch();location.reload(true);">
                                            <option <?php if($selsearch==1) echo 'selected '; ?>value="1">Actor</option>
                                            <option <?php if($selsearch==2) echo 'selected '; ?>value="2">Movie</option>
                                        </select>
                                    </label>
                                </div>
                            </td>
                            <td>&nbsp;</td>
                            <td width='300px'>
                                <input type="text" placeholder="Write your search" name="txtsrch" id="srch-term">                    
                            </td>
                            <td>&nbsp;</td>
                            <td width='10%'>
                                <input type="submit" name="btnsub" class="btn btn-default" value="Search">
                            </td>
                            <td>
                                &nbsp;&nbsp;
                            </td>
                            <td width='80%'>
                                <table width='100%'>
                                    <tr>
                                        <td class='active'>
                                            Developed by: John Belalcazar for AlertLogic Test1
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                    <div class="input-group-btn">
                        
                    </div>
		</div>
	    </form>
            
        </div>
        
        <hr>
    
        <div class="container">

            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">
                                Welcome
                            </a>
                        </h4>
                    </div>
                    <div id="collapseOne" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div align='center'>
                                <img src='images/var_8_0_tmdb-logo-2_Bree.png' alt='themoviedb logo' />
                            </div>

                            <p class='label-info' align='center'>
                                Please choose your type of search and then write your query by providing an actor or movie name in the top box.<br>
                                Results will be displayed below.
                            </p>

                            <div class='alert-warning'>
                            Name: John Harold Belalcazar Lozano<br>
                            Email: <a href='mailto:jhbelalc@icloud.com'>jhbelalc@icloud.com</a><br>
                            Cell: +57 3144438877<br>
                            Skype: jhbelalc<br>    
                            </div>        
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo">
                                Actors List
                            </a>
                        </h4>
                    </div>
                    <div id="collapseTwo" class="panel-collapse collapse">
                        <div class="panel-body" id="tblActors">
                            <p class="alert-info" align="center">Please click on the actor's name to display movies, click on back button on your browser to return.</p>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseThree">
                                Movies List
                            </a>
                        </h4>
                    </div>
                    <div id="collapseThree" class="panel-collapse collapse">
                        <div class="panel-body" id="tblMovies">
                            <p class="alert-info" align="center">Please click on the movie name to display summary and videos if available, click on back button on your browser to return to Actor list.</p>
                        </div>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#collapseFour">
                                Movie Detail
                            </a>
                        </h4>
                    </div>
                    <div id="collapseFour" class="panel-collapse collapse">
                        <div class="panel-body" id="tblDetMovie">
                            <p class="alert-info" align="center">Details of the movie, please click your back button on your browser to return to the movie list.</p>
                        </div>
                    </div>
                </div>
            </div>  

        </div>       
        
        <script src="js/fancyBox/lib/jquery-1.10.1.min.js" type="text/javascript"></script>
        <script src="js/fancyBox/lib/jquery.mousewheel-3.0.6.pack.js" type="text/javascript"></script>
        <script src="js/fancyBox/source/jquery.fancybox.js" type="text/javascript"></script>
        <script src="js/fancyBox/source/helpers/jquery.fancybox-media.js" type="text/javascript"></script>
        <script src="js/fancyBox/source/helpers/jquery.fancybox-thumbs.js" type="text/javascript"></script>
        <script src="js/fancyBox/source/helpers/jquery.fancybox-buttons.js" type="text/javascript"></script>
        <script src="css/Bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="js/scripts.js" type="text/javascript"></script>
        <script src="js/typeahead.min.js" type="text/javascript"></script>
        <script src="js/js.cookie.js" type="text/javascript"></script>
        <script type='text/javascript'>
            $(document).ready(function(){
                var mode=<?php echo "'".$actorbi."'" ?>;
                switch (mode){
                    case "1":
                        $("#collapseOne").collapse("hide");
                        $("#collapseTwo").collapse("show");
                        $("#collapseThree").collapse("hide");
                        $("#collapseFour").collapse("hide");
                        var tab=<?php echo '"'.$tableactors.'"' ?>;
                        $("#tblActors").append(tab);
                        break;
                    case "2":
                        $("#collapseOne").collapse("hide");
                        $("#collapseTwo").collapse("hide");
                        $("#collapseThree").collapse("show");
                        $("#collapseFour").collapse("hide");
                        var tab=<?php echo '"'.$tablemovies.'"' ?>;
                        $("#tblMovies").append(tab);
                        break;
                    case "3":
                        $("#collapseOne").collapse("hide");
                        $("#collapseTwo").collapse("hide");
                        $("#collapseThree").collapse("hide");
                        $("#collapseFour").collapse("show");
                        var tab=<?php echo '"'.$tablemovied.'"' ?>;
                        $("#tblDetMovie").append(tab);                        
                        break;
                    default:
                        $("#collapseOne").collapse("show");
                        $("#collapseTwo").collapse("hide");
                        $("#collapseThree").collapse("hide");
                        $("#collapseFour").collapse("hide");
                        break;
                }
            });
        </script>
 </body>
</html>
