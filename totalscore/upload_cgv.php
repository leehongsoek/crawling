<?
    set_time_limit(0) ;

    function Delete_cgv_movies($_connect)
    {
        $sQuery = "   DELETE FROM cgv_movies " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_cgv_movies($moviecode, $moviename, $releasedate, $_connect)
    {
        $moviename = iconv("UTF-8", "EUC-KR", $moviename);

        $sQuery = "   INSERT INTO cgv_movies
                                  ( moviecode, moviename, releasedate )
                           VALUES ('$moviecode', '$moviename', '$releasedate')
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Delete_cgv_regions($_connect)
    {
        $sQuery = "   DELETE FROM cgv_regions " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_cgv_regions($regioncode, $regionname, $_connect)
    {
        $regionname  = iconv("UTF-8", "EUC-KR", $regionname);

        $sQuery = "   INSERT INTO cgv_regions
                                  ( regioncode, regionname )
                           VALUES ('$regioncode', '$regionname')
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Delete_cgv_theater($_connect)
    {
        $sQuery = "   DELETE FROM cgv_theater " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_cgv_theater($theatercode, $regioncode, $regionname, $theatername, $_connect)
    {
        $regionname  = iconv("UTF-8", "EUC-KR", $regionname);
        $theatername = iconv("UTF-8", "EUC-KR", $theatername);

        $sQuery = "   INSERT INTO cgv_theater
                                  ( theatercode, regioncode, regionname, theatername )
                           VALUES ('$theatercode', '$regioncode', '$regionname', '$theatername')
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }


    function Delete_cgv_ticketingdata($_connect)
    {
        $sQuery = "   DELETE FROM cgv_tickecting1 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;

        $sQuery = "   DELETE FROM cgv_tickecting2 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;

        $sQuery = "   DELETE FROM cgv_tickecting3 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_cgv_ticketingdata1($playdate, $theaterkey, $moviecode, $moviename, $moviegrade, $movieplaying, $moviegenre, $movieruntime, $moviereleasedate, $_connect)
    {
        $moviename      = iconv("UTF-8", "EUC-KR", $moviename);
        $moviegrade     = iconv("UTF-8", "EUC-KR", $moviegrade);
        $movieplaying   = iconv("UTF-8", "EUC-KR", $movieplaying);
        $moviegenre     = iconv("UTF-8", "EUC-KR", $moviegenre);
        $movieruntime   = iconv("UTF-8", "EUC-KR", $movieruntime);

        //cho $cinemaid .":".  $roomid ."/";

        $sQuery = "   SELECT theatername
                        FROM cgv_theater
                       WHERE theatercode =  '$theaterkey'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryTheater = mysql_query($sQuery,$_connect) ;
        if  ($ArrTheater = mysql_fetch_array($QryTheater))
        {
            $theatername = $ArrTheater["theatername"];
        }

        $sQuery = "   INSERT INTO cgv_tickecting1
                                  ( playdate, theatercode, moviecode, theatername ,moviename )
                           VALUES ( '$playdate', '$theaterkey', '$moviecode', '$theatername', '$moviename' )
                  " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }


    function Insert_cgv_ticketingdata2($playdate, $theaterkey, $moviecode, $gunbunno, $filmtype, $roomfloor, $totalseat, $_connect)
    {
        $filmtype     = iconv("UTF-8", "EUC-KR", $filmtype);
        $roomfloor    = iconv("UTF-8", "EUC-KR", $roomfloor);
        $totalseat    = iconv("UTF-8", "EUC-KR", $totalseat);

        $sQuery = "   SELECT theatername
                        FROM cgv_theater
                       WHERE theatercode =  '$theaterkey'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryTheater = mysql_query($sQuery,$_connect) ;
        if  ($ArrTheater = mysql_fetch_array($QryTheater))
        {
            $theatername = $ArrTheater["theatername"];
        }

        $sQuery = "   SELECT moviename
                        FROM cgv_movies
                       WHERE moviecode =  '$moviecode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryMovie = mysql_query($sQuery,$_connect) ;
        if  ($ArrMovie = mysql_fetch_array($QryMovie))
        {
            $moviename = $ArrMovie["moviename"];
        }

        //cho $cinemaid .":".  $roomid ."/";
        $sQuery = "   INSERT INTO cgv_tickecting2
                                  ( playdate, theatercode, moviecode, gunbunno, theatername, moviename, filmtype, roomfloor, totalseat  )
                           VALUES ( '$playdate', '$theaterkey', '$moviecode', $gunbunno, '$theatername', '$moviename', '$filmtype', '$roomfloor', '$totalseat' )
                  " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }


    function Insert_cgv_ticketingdata3($playdate, $theaterkey, $moviecode, $gunbunno, $timeno, $playtime, $playinfo, $playetc, $_connect)
    {
        $playinfo = iconv("UTF-8", "EUC-KR", $playinfo);
        $playetc  = iconv("UTF-8", "EUC-KR", $playetc);

        $sQuery = "   SELECT theatername
                        FROM cgv_theater
                       WHERE theatercode =  '$theaterkey'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryTheater = mysql_query($sQuery,$_connect) ;
        if  ($ArrTheater = mysql_fetch_array($QryTheater))
        {
            $theatername = $ArrTheater["theatername"];
        }

        $sQuery = "   SELECT moviename
                        FROM cgv_movies
                       WHERE moviecode =  '$moviecode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryMovie = mysql_query($sQuery,$_connect) ;
        if  ($ArrMovie = mysql_fetch_array($QryMovie))
        {
            $moviename = $ArrMovie["moviename"];
        }

        $sQuery = "   SELECT filmtype
                        FROM cgv_tickecting2
                       WHERE theatercode = '$theaterkey'
                         AND moviecode =  '$moviecode'
                         AND gunbunno = gunbunno
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryTickect = mysql_query($sQuery,$_connect) ;
        if  ($ArrTickect = mysql_fetch_array($QryTickect))
        {
            $filmtype = $ArrTickect["filmtype"];
        }

        //cho $cinemaid .":".  $roomid ."/";
        $sQuery = "   INSERT INTO cgv_tickecting3
                                  ( playdate, theatercode, moviecode, gunbunno, timeno, theatername, moviename, filmtype, playtime, playinfo, playetc )
                           VALUES ( '$playdate', '$theaterkey', '$moviecode', $gunbunno, $timeno, '$theatername', '$moviename', '$filmtype', '$playtime', '$playinfo', '$playetc' )
                  " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    /*
    function Insert_cgv_ticketingdata($cinemaid, $roomid, $moviecode, $cinemaname, $groupname, $screenname, $moviename, $movietype, $moviegubun, $playdt, $starttime, $endtime, $bookingseatcount, $totalseatcount, $_connect)
    {
        $cinemaname  = iconv("UTF-8", "EUC-KR", $cinemaname);
        $groupname   = iconv("UTF-8", "EUC-KR", $groupname);
        $screenname  = iconv("UTF-8", "EUC-KR", $screenname);
        $moviename   = iconv("UTF-8", "EUC-KR", $moviename);
        $moviegubun  = iconv("UTF-8", "EUC-KR", $moviegubun);

        $sQuery = "   INSERT INTO lotte_ticketingdata
                                  ( cinemaid, roomid, moviecode, cinemaname, groupname, screenname, moviename, movietype, moviegubun, playdt, starttime, endtime, bookingseatcount, totalseatcount )
                           VALUES ( '$cinemaid', '$roomid', '$moviecode', '$cinemaname', '$groupname', '$screenname', '$moviename', '$movietype', '$moviegubun', '$playdt', '$starttime', '$endtime', $bookingseatcount, $totalseatcount )
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    */

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정

    $connect = dbconn() ;           // {[데이터 베이스]} : 연결

    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    include "lib/JSON.php";           // json 리더 라이브러리

    $json = new Services_JSON();        // create a new instance of Services_JSON


    $noslash = stripcslashes( $_POST['movies'] ); // \" -> "
    $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    //var_dump($json);

    Delete_cgv_movies($connect);
    foreach($jvalue as $key => $val)
    {
        Insert_cgv_movies($key,$val[0],$val[1],$connect);
        /*
        echo $key . ': ';
        foreach($val as $val1)
        {
            echo $val1;
            echo ',';
        }
        echo '\n\r';
        */
    }

    $noslash = stripcslashes( $_POST['regions'] ); // \" -> "
    $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    //var_dump($json);

    Delete_cgv_regions($connect);
    foreach($jvalue as $key => $val)
    {
        Insert_cgv_regions($key,$val,$connect);
    }


    $noslash = stripcslashes( $_POST['theater'] ); // \" -> "
    $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    // var_dump($noslash);

    Delete_cgv_theater($connect);
    foreach($jvalue as $key => $val)
    {
        Insert_cgv_theater($key,$val[0],$val[1],$val[2],$connect);
    }


    $noslash = stripcslashes( $_POST['ticketingdays'] ); // \" -> "
    $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    //var_dump($noslash);

    Delete_cgv_ticketingdata($connect);
    foreach($jvalue as $key => $val)
    {
        $playday = $key ;
        $jvalue  = $val ;

        foreach($jvalue as $key => $val)
        {
            // var_dump($key);
            // var_dump($val);
            $theaterkey = $key ;
            $jvalue = $val ;

            foreach($jvalue as $key => $val)
            {
                $moviecode = $key ;

                Insert_cgv_ticketingdata1($playday, $theaterkey, $moviecode, $val[0], $val[1], $val[2], $val[3], $val[4], $val[5], $connect);

                $jvalue = $val[6] ;
                foreach($jvalue as $key => $val)
                {
                    $gunbunno = $key ;
                    Insert_cgv_ticketingdata2($playday, $theaterkey,$moviecode,$gunbunno,$val[0],$val[1],$val[2],$connect);

                    $jvalue = $val[3] ;
                    foreach($jvalue as $key => $val)
                    {
                        $timeno = $key ;
                        Insert_cgv_ticketingdata3($playday, $theaterkey,$moviecode,$gunbunno,$timeno,$val[0],$val[1],$val[2],$connect);

                        $jvalue = $val[3] ;
                    }
                }
            }
        }
    }
    $noslash = stripcslashes( $_POST['ticketingdata'] ); // \" -> "

    mysql_close($connect) ;      // {[데이터 베이스]} : 단절

?>