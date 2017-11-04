<?
    set_time_limit(0) ;

    function Delete_mega_movies($_connect)
    {
        $sQuery = "   DELETE FROM mega_movies " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_mega_movies($moviecode, $releasedate, $moviegbn, $moviename, $_connect)
    {
        $moviegbn  = iconv("UTF-8", "EUC-KR", $moviegbn);
        $moviename = iconv("UTF-8", "EUC-KR", $moviename);

        $sQuery = "   INSERT INTO mega_movies
                                  ( moviecode, releasedate, moviegbn, moviename )
                           VALUES ('$moviecode', '$releasedate', '$moviegbn', '$moviename')
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Delete_mega_regions($_connect)
    {
        $sQuery = "   DELETE FROM mega_regions " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_mega_regions($regioncode, $regionname, $_connect)
    {
        $regionname  = iconv("UTF-8", "EUC-KR", $regionname);

        $sQuery = "   INSERT INTO mega_regions
                                  ( regioncode, regionname )
                           VALUES ('$regioncode', '$regionname')
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Delete_mega_cinemas($_connect)
    {
        $sQuery = "   DELETE FROM mega_theater " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_mega_cinemas($cinemacode, $regioncode, $cinemaname, $kofcinemacode   , $_connect)
    {
        $cinemaname = iconv("UTF-8", "EUC-KR", $cinemaname);

        $sQuery = "   INSERT INTO mega_cinemas
                                  ( cinemacode, regioncode, cinemaname, kofcinemacode )
                           VALUES ('$cinemacode', '$regioncode', '$cinemaname', '$kofcinemacode')
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }


    function Delete_mega_ticketingdata($_connect)
    {
        $sQuery = "   DELETE FROM mega_tickecting1 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;

        $sQuery = "   DELETE FROM mega_tickecting2 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;

        $sQuery = "   DELETE FROM mega_tickecting3 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_mega_ticketingdata1($playdate, $cinemacode, $moviecode, $moviename, $viewgradename, $_connect)
    {
        $moviename      = iconv("UTF-8", "EUC-KR", $moviename);
        $viewgradename  = iconv("UTF-8", "EUC-KR", $viewgradename);

        $sQuery = "   SELECT cinemaname
                        FROM mega_cinemas
                       WHERE cinemacode =  '$cinemacode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryCinema = mysql_query($sQuery,$_connect) ;
        if  ($ArrCinema = mysql_fetch_array($QryCinema))
        {
            $cinemaname = $ArrCinema["cinemaname"];
        }


        $sQuery = "   INSERT INTO mega_tickecting1
                                  ( playdate, cinemacode, moviecode, cinemaname, moviename )
                           VALUES ( '$playdate', '$cinemacode', '$moviecode', '$cinemaname', '$moviename' )
                  " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_mega_ticketingdata2($playdate, $cinemacode, $moviecode, $roomno, $cntRoom, $cinemaroom, $moviegubun, $_connect)
    {
        $cinemaroom   = iconv("UTF-8", "EUC-KR", $cinemaroom);
        $moviegubun   = iconv("UTF-8", "EUC-KR", $moviegubun);

        $sQuery = "   SELECT cinemaname
                        FROM mega_cinemas
                       WHERE cinemacode =  '$cinemacode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryCinema = mysql_query($sQuery,$_connect) ;
        if  ($ArrCinema = mysql_fetch_array($QryCinema))
        {
            $cinemaname = $ArrCinema["cinemaname"];
        }

        $sQuery = "   SELECT moviename
                        FROM mega_movies
                       WHERE moviecode =  '$moviecode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryMovie = mysql_query($sQuery,$_connect) ;
        if  ($ArrMovie = mysql_fetch_array($QryMovie))
        {
            $moviename = $ArrMovie["moviename"];
        }

        $sQuery = "   INSERT INTO mega_tickecting2
                                  ( playdate, cinemacode, moviecode, roomno, cinemaname, moviename, cinemaroom, moviegubun )
                           VALUES ( '$playdate', '$cinemacode', '$moviecode', $roomno, '$cinemaname', '$moviename', '$cinemaroom', '$moviegubun' )
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;

    }

    function Insert_mega_ticketingdata3($playdate, $cinemacode, $moviecode, $roomno, $timeno, $starttime, $endtime, $timegbn, $seat, $_connect)
    {
        $timegbn = iconv("UTF-8", "EUC-KR", $timegbn);
        $seat    = iconv("UTF-8", "EUC-KR", $seat);

        $sQuery = "   SELECT cinemaname
                        FROM mega_cinemas
                       WHERE cinemacode =  '$cinemacode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryCinema = mysql_query($sQuery,$_connect) ;
        if  ($ArrCinema = mysql_fetch_array($QryCinema))
        {
            $cinemaname = $ArrCinema["cinemaname"];
        }

        $sQuery = "   SELECT moviename
                        FROM mega_movies
                       WHERE moviecode =  '$moviecode'
                  " ; // echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryMovie = mysql_query($sQuery,$_connect) ;
        if  ($ArrMovie = mysql_fetch_array($QryMovie))
        {
            $moviename = $ArrMovie["moviename"];
        }

        $sQuery = "   SELECT moviegubun
                        FROM mega_tickecting2
                       WHERE playdate   =  '$playdate'
                         AND cinemacode =  '$cinemacode'
                         AND moviecode  =  '$moviecode'
                         AND roomno     =  '$roomno'
                  " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
        $QryTickecting2 = mysql_query($sQuery,$_connect) ;
        if  ($ArrTickecting2 = mysql_fetch_array($QryTickecting2))
        {
            $moviegubun = $ArrTickecting2["moviegubun"];
        }

        $sQuery = "   INSERT INTO mega_tickecting3
                                  ( playdate, cinemacode, moviecode, roomno, timeno, cinemaname, moviename, moviegubun, starttime, endtime, timegbn, seat  )
                           VALUES ( '$playdate', '$cinemacode', '$moviecode', $roomno, $timeno, '$cinemaname', '$moviename', '$moviegubun', '$starttime', '$endtime', '$timegbn', '$seat' )
                  " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;

    }

    /*

















    */

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정

    $connect = dbconn() ;           // {[데이터 베이스]} : 연결

    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    include "lib/JSON.php";           // json 리더 라이브러리

    $json = new Services_JSON();        // create a new instance of Services_JSON


    $noslash = stripcslashes( $_POST['movies'] ); // \" -> "
    $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    //var_dump($json);

    Delete_mega_movies($connect);
    foreach($jvalue as $key => $val)
    {
        Insert_mega_movies($key,$val[0],$val[1],$val[2],$connect);
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

    Delete_mega_regions($connect);
    foreach($jvalue as $key => $val)
    {
        Insert_mega_regions($key,$val,$connect);
    }

    $noslash = stripcslashes( $_POST['cinemas'] ); // \" -> "
    $jvalue1 = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    Delete_mega_cinemas($connect);
    foreach($jvalue1 as $key => $val)
    {
        Insert_mega_cinemas($key,$val[0],$val[1],$val[2],$connect);
    }


    $noslash = stripcslashes( $_POST['ticketingdata'] ); // \" -> "
    $jvalue1 = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

    Delete_mega_ticketingdata($connect);

// echo '+++++++++++++++';

    // var_dump($noslash);

    foreach($jvalue1 as $key => $val)
    {

        $playdate = $key ;
        $jvalue = $val ;

        //echo 'key:'.$key;
        foreach($jvalue as $key => $val)
        {

            $cinemacode = $key ;
            $jvalue = $val ;


            foreach($jvalue as $key => $val)
            {
                $moviecode = $key ;
                $jvalue = $val ;

                // $sQuery = $playdate.' / '.$cinemacode.' / '.$moviecode.' // '.iconv("UTF-8", "EUC-KR", $val[0]).','.$val[1].' ++';
                // echo $sQuery;

                Insert_mega_ticketingdata1($playdate,$cinemacode,$moviecode,$val[0],$val[1],$connect);

                $jvalue = $val[2] ;

                foreach($jvalue as $key => $val)
                {
                    $roomno = $key ;
                    $jvalue = $val ;

                    // var_dump($val);

                    Insert_mega_ticketingdata2($playdate,$cinemacode,$moviecode,$roomno,$val[0],$val[1],$val[2],$connect);

                    // var_dump($val[4]);

                    $jvalue = $val[4] ;

                    $timeno = 0 ;

                    foreach($jvalue as $key => $val)
                    {

                        $starttime = $key ;


                        // var_dump($val);

                        $timeno ++ ;

                        Insert_mega_ticketingdata3($playdate,$cinemacode,$moviecode,$roomno,$timeno,$starttime,$val[0],$val[1],$val[2],$connect);
                    }
                }
            }

        }

    }


    mysql_close($connect) ;      // {[데이터 베이스]} : 단절

?>