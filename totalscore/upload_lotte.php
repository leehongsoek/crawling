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


        function Delete_lotte_ticketing($_connect)
        {
            $sQuery = "   DELETE FROM lotte_tickecting1 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;

            $sQuery = "   DELETE FROM lotte_tickecting2 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;

            $sQuery = "   DELETE FROM lotte_tickecting3 " ; //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }

        function Insert_lotte_ticketing1($playdate, $cinemaId, $_connect)
        {
            $sQuery = "   SELECT cinemaname
                            FROM lotte_cinemas
                           WHERE cinemaid = '$cinemaId'
                      " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
            $QryCinemas = mysql_query($sQuery,$_connect) ;
            if  ($ArrCinemas = mysql_fetch_array($QryCinemas))
            {
                $cinemaname = $ArrCinemas["cinemaname"] ;
            }

            $sQuery = "   INSERT INTO lotte_tickecting1
                                      ( playdate, cinemaid, cinemaname )
                               VALUES ( '$playdate', '$cinemaId', '$cinemaname' )
                      " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }

        function Insert_lotte_ticketing2($playdate, $cinemaId, $screenid, $screennamekr, $totalseatcount, $_connect)
        {
            $sQuery = "   SELECT cinemaname
                            FROM lotte_cinemas
                           WHERE cinemaid = '$cinemaId'
                      " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
            $QryCinemas = mysql_query($sQuery,$_connect) ;
            if  ($ArrCinemas = mysql_fetch_array($QryCinemas))
            {
                $cinemaname = $ArrCinemas["cinemaname"] ;
            }

            $screennamekr  = iconv("UTF-8", "EUC-KR", $screennamekr);

            $sQuery = "   INSERT INTO lotte_tickecting2
                                      (
                                       playdate
                                      ,cinemaid
                                      ,screenid
                                      ,cinemaname
                                      ,screennamekr
                                      ,totalseatcount
                                      )
                               VALUES (
                                       '$playdate'
                                      ,'$cinemaId'
                                      ,'$screenid'
                                      ,'$cinemaname'
                                      ,'$screennamekr'
                                      ,$totalseatcount
                                      )
                      " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
            mysql_query($sQuery,$_connect) ;
        }

        function Insert_lotte_ticketing3( $playdate, $cinemaId, $screenid, $degreeNo, $screennamekr, $starttime, $endtime, $bookingseatcount, $moviecode, $filmnamekr, $gubun, $_connect)
        {
            $sQuery = "   SELECT cinemaname
                            FROM lotte_cinemas
                           WHERE cinemaid = '$cinemaId'
                      " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
            $QryCinemas = mysql_query($sQuery,$_connect) ;
            if  ($ArrCinemas = mysql_fetch_array($QryCinemas))
            {
                $cinemaname = $ArrCinemas["cinemaname"] ;
            }

            $screennamekr  = iconv("UTF-8", "EUC-KR", $screennamekr);

            $sQuery = "   SELECT moviename
                            FROM lotte_moviedata
                           WHERE moviecode = '$moviecode'
                      " ;  //echo iconv("EUC-KR", "UTF-8",$sQuery);
            $QryMovie = mysql_query($sQuery,$_connect) ;
            if  ($ArrMovie = mysql_fetch_array($QryMovie))
            {
                $moviename = $ArrMovie["moviename"] ;
            }


            $sQuery = "   INSERT INTO lotte_tickecting3
                                      (
                                       playdate
                                      ,cinemaid
                                      ,screenid
                                      ,degreeNo
                                      ,cinemaname
                                      ,screennamekr
                                      ,starttime
                                      ,endtime
                                      ,bookingseatcount
                                      ,moviecode
                                      ,moviename
                                      ,filmnamekr
                                      ,gubun
                                      )
                               VALUES (
                                       '$playdate'
                                      ,'$cinemaId'
                                      ,'$screenid'
                                      ,'$degreeNo'
                                      ,'$cinemaname'
                                      ,'$screennamekr'
                                      ,'$starttime'
                                      ,'$endtime'
                                      ,$bookingseatcount
                                      ,'$moviecode'
                                      ,'$moviename'
                                      ,'$filmnamekr'
                                      ,'$gubun'
                                      )
                      " ;  //if  ($moviecode==11783) echo iconv("EUC-KR", "UTF-8",$sQuery);
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

        Delete_lotte_ticketing($connect);
        foreach($jvalue as $key => $val) // dicTicketingData
        {
            $playdate = $key;
            $jvalue   = $val[0] ;

            foreach($jvalue as $key => $val) // dicTeather
            {
                $cinemaId = $key;

                //var_dump($cinemaId);
                //var_dump($val[0]);

                Insert_lotte_ticketing1($playdate, $cinemaId, $connect);

                $jvalue = $val[0] ;
                foreach($jvalue as $key => $val) // dicScreen
                {
                    $screenid       = $key ;
                    $screennamekr   = $val[0] ;
                    $totalseatcount = $val[1] ;

                    Insert_lotte_ticketing2($playdate, $cinemaId, $screenid, $screennamekr, $totalseatcount, $connect);

                    //var_dump($val[2]);
                    //var_dump($screennamekr);
                    //var_dump(count($val)-2);
                    //echo $screennamekr."\n";

                    for ($i=2 ; $i<count($val) ; $i++)
                    {
                        $jvalue = $val[$i] ;

                        foreach($jvalue as $key => $val2) // dicTime
                        {
                            $degreeNo         = $key ;
                            $starttime        = $val2[0] ;
                            $endtime          = $val2[1] ;
                            $bookingseatcount = $val2[2] ;
                            $moviecode        = $val2[3] ;
                            $filmnamekr       = $val2[4] ;
                            $gubun            = $val2[5] ;

                            /*
                            if  ($moviecode==12041)
                            {
                                //var_dump($moviecode);
                                echo "  " . $starttime ."~". $screenid."\n";
                            }
                            */

                            Insert_lotte_ticketing3( $playdate, $cinemaId, $screenid, $degreeNo, $screennamekr, $starttime, $endtime, $bookingseatcount, $moviecode, $filmnamekr, $gubun, $connect);

                            //$moviecode = $key ;
                        }
                    }

                    //$moviecode = $key ;
                }

                //$moviecode = $key ;
            }
            //$cinemaid  = substr($key, 0, 4);
            //$roomid    = substr($key, 4, 2);
            //$moviecode = substr($key, 6, 5);
//
            //Insert_lotte_ticketingdata($cinemaid,$roomid,$moviecode,$val[0],$val[1],$val[2],$val[3],$val[4],$val[5],$val[6],$val[7],$val[8],$val[9],$val[10],$connect);
        }

        mysql_close($connect) ;      // {[데이터 베이스]} : 단절

    ?>