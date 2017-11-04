    <?
        set_time_limit(0) ;

        function Delete_lotte_moviedata($_connect)
        {
            $sQuery = "   DELETE FROM lotte_moviedata " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }

        function Insert_lotte_moviedata($moviecode, $moviename, $moviegenrename, $bookingyn, $releasedate, $viewgradename, $_connect)
        {
            $moviename      = iconv("UTF-8", "EUC-KR", $moviename);
            $moviegenrename = iconv("UTF-8", "EUC-KR", $moviegenrename);
            $viewgradename  = iconv("UTF-8", "EUC-KR", $viewgradename);

            $sQuery = "   INSERT INTO lotte_moviedata
                                      ( moviecode, moviename, moviegenrename, bookingyn, releasedate, viewgradename )
                               VALUES ('$moviecode', '$moviename', '$moviegenrename', '$bookingyn', '$releasedate', '$viewgradename')
                      " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }


        function Delete_lotte_cinemas($_connect)
        {
            $sQuery = "   DELETE FROM lotte_cinemas " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }

        function Insert_lotte_cinemas($cinemaid, $special_yn, $sortsequence, $cinemaname, $_connect)
        {
            $cinemaname  = iconv("UTF-8", "EUC-KR", $cinemaname);

            $sQuery = "   INSERT INTO lotte_cinemas
                                      ( cinemaid, special_yn, sortsequence, cinemaname )
                               VALUES ('$cinemaid', '$special_yn', $sortsequence, '$cinemaname')
                      " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }


        function Delete_lotte_ticketingdata($_connect)
        {
            $sQuery = "   DELETE FROM lotte_ticketingdata " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }

        function Insert_lotte_ticketingdata($cinemaid, $roomid, $moviecode, $cinemaname, $groupname, $screenname, $moviename, $movietype, $moviegubun, $playdt, $starttime, $endtime, $bookingseatcount, $totalseatcount, $_connect)
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


        include "inc/config.php";       // {[데이터 베이스]} : 환경설정

        $connect = dbconn() ;           // {[데이터 베이스]} : 연결

        mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

        include "lib/JSON.php";           // json 리더 라이브러리

        $json = new Services_JSON();        // create a new instance of Services_JSON


        $noslash = stripcslashes( $_POST['moviedata'] ); // \" -> "
        $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

        //var_dump($json);

        Delete_lotte_moviedata($connect);
        foreach($jvalue as $key => $val)
        {
            Insert_lotte_moviedata($key,$val[0],$val[1],$val[2],$val[3],$val[4],$connect);
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

        $noslash = stripcslashes( $_POST['cinemas'] ); // \" -> "
        $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

        //var_dump($json);

        Delete_lotte_cinemas($connect);
        foreach($jvalue as $key => $val)
        {
            Insert_lotte_cinemas($key,$val[0],$val[1],$val[2],$connect);
        }


        $noslash = stripcslashes( $_POST['ticketingdata'] ); // \" -> "
        $jvalue = $json->decode($noslash); // json형태의 스트링을 php 배열로 변환한다.

        //var_dump($_POST['ticketingdata']);

        Delete_lotte_ticketingdata($connect);
        foreach($jvalue as $key => $val)
        {
            $cinemaid  = substr($key, 0, 4);
            $roomid    = substr($key, 4, 2);
            $moviecode = substr($key, 6, 5);

            Insert_lotte_ticketingdata($cinemaid,$roomid,$moviecode,$val[0],$val[1],$val[2],$val[3],$val[4],$val[5],$val[6],$val[7],$val[8],$val[9],$val[10],$connect);
        }

        mysql_close($connect) ;      // {[데이터 베이스]} : 단절

    ?>