<?
    set_time_limit(0) ;

    function Delete_WrkJob_All($_connect)
    {
        $sQuery = "  DELETE FROM wrk_job WHERE 1=1 " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Insert_WrkJob($JobCode,$Status,$Percent,$_connect)
    {
        $sQuery = "   INSERT INTO wrk_job
                             ( JobCode, Status, Percent )
                      VALUES ( '$JobCode','$Status',$Percent)
                  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Update_WrkJob($JobCode,$Status,$Percent,$_connect)
    {
        //$Status = iconv("UTF-8", "EUC-KR",$Status) ;
        $sQuery = "   UPDATE wrk_job
                         SET JobCode = '$JobCode',
                             Status  = '$Status',
                             Percent = $Percent
                       WHERE JobCode = '$JobCode'
                  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }

    function Delete_WrkJob($JobCode,$_connect)
    {
        $sQuery = "  DELETE FROM wrk_job
                           WHERE JobCode = '$JobCode'
                  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
        mysql_query($sQuery,$_connect) ;
    }
/*
function unhtmlspecialchars($str)
{
    $trans = get_html_translation_table();
    $trans = array_flip($trans);
    $str = strtr($str, $trans);
    return $str;
}
*/
    include "inc/config.php";       // {[데이터 베이스]} : 환경설정

    $connect = dbconn() ;           // {[데이터 베이스]} : 연결

    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    include "lib/get_remotefile.php"; // 클로링 라이브러리
    include "lib/JSON.php";           // json 리더 라이브러리

    $Jobing = "";

    $cntTeather = 405;

    Delete_WrkJob_All($connect);
    //Insert_WrkJob('A','Start!',0,$connect);
    //Insert_WrkJob('B','Start!',0,$connect);
    //Insert_WrkJob('C','Start!',0,$connect);
    //Insert_WrkJob('D','Start!',0,$connect); // 박스오피스
    //Insert_WrkJob('E','Start!',0,$connect);

    $sQuery = "    SELECT JobCode
                     FROM wrk_job
                 ORDER BY JobCode
              " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
    $QryWrkJob = mysql_query($sQuery,$connect) ;
    while  ($ArrWrkJob = mysql_fetch_array($QryWrkJob))
    {
        $JobCode = $ArrWrkJob["JobCode"] ;


        // A : "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterInfoList.do";
        //
        //

        $Jobing = "A";

        if  ($JobCode == $Jobing)
        {
              $exit = false ;

              $cntTeather = 0 ;

              for($p=1;;$p++) //41 페이지를 끝까지 돈다.
              {
                $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterInfoList.do";
                $vars = array(
                             "pageIndex"   => $p,
                             "sPermYn"     => "Y",
                             "sJoinYn"     => "Y",
                             "sSaleStat"   => "018201",
                             "theaCd"      => "",
                             "sTheaNm"     => "",
                             "sTheaCd"     => "",
                             "sWideareaCd" => "",
                             "sBasareaCd"  => "",
                             "sSenderCd"   => ""
                             );
                $retValue = get_remotefile($url,$vars);

                $arrLine1 = split("\n",$retValue); // 한줄씩 분리..
                for ($i=0 ; $i< sizeof($arrLine1) ; $i++)
                {
                    $line1 = trim($arrLine1[$i]) ;
                    //echo "<br>".$arrLine1[$i];
                    // 극장명 캐치
                    if  (strpos($line1, "javascript:fn_detail(event, $(this),")!==false)
                    {
                        $TheatherCode = substr($line1,116,6) ;

                        $sp = strpos($line1, "return false;&quot;&gt;") + 23;
                        $ep = strpos($line1, "&lt;/a&gt;&lt;/td&gt;");

                        $TheatherName = substr($line1,$sp,$ep-$sp) ;
                        //echo "<br>".$TheatherCode.", ".$TheatherName; // 극장 코드, 극장명

                        $TheatherName_ = iconv("UTF-8", "EUC-KR",$TheatherName);  // 한글처리

                        $sQuery = "DELETE FROM kofic_theather
                                         WHERE Code = '".$TheatherCode."'
                                  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
                        mysql_query($sQuery,$connect) ;

                        $sQuery = "INSERT INTO kofic_theather
                                        VALUES ('".$TheatherCode."'
                                               ,'".$TheatherName_."'
                                               ,( SELECT IFNULL(SUM(Seat),0)
                                                    FROM kofic_seat
                                                   WHERE TheatherCd = '".$TheatherCode."' )
                                               )
                                  " ;//echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
                        mysql_query($sQuery,$connect) ;

                        $sQuery = "UPDATE kofic_fix_theather
                                      SET TheatherName = '".$TheatherName_."'
                                    WHERE Code = '".$TheatherCode."'
                                  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
                        mysql_query($sQuery,$connect) ;


                        Update_WrkJob($Jobing,"(".$cntTeather++.")".$TheatherName_,0,$connect);
                        //echo  "<br>".iconv("EUC-KR", "UTF-8",$TheatherName);
                    }

                    if  (strpos($arrLine1[$i], "검색된 데이터가 존재하지 않습니다.")!==false)
                    {
                        $exit = true ;
                        break;
                    }
                }

                if($exit == true) break;
              }

              // 좌석수가 있으면 좌석수를 가지고 온다.
              $sQuery = "UPDATE kofic_theather tht
                            SET Seat =  (SELECT IFNULL(SUM(Seat),0)
                                           FROM kofic_seat
                                          WHERE TheatherCd = tht.Code
                                        )
                        " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
              mysql_query($sQuery,$connect) ;


              // 영진위에서 삭제된 극장 자료는 관계되는 다른 정보도 같이 지운도록 처리 ..
              $sQuery = "DELETE FROM kofic_seat
                               WHERE TheatherCd IN (
                                                        SELECT b.Code
                                                          FROM kofic_theather     a
                                                    RIGHT JOIN kofic_fix_theather b
                                                            ON a.Code = b.Code
                                                         WHERE a.Code IS NULL
                                                   )
                        ";
              mysql_query($sQuery,$connect) ;

              $sQuery = "DELETE FROM kofic_showtime
                               WHERE TheatherCd IN (
                                                        SELECT b.Code
                                                          FROM kofic_theather     a
                                                    RIGHT JOIN kofic_fix_theather b
                                                            ON a.Code = b.Code
                                                         WHERE a.Code IS NULL
                                                   )
                        ";
              mysql_query($sQuery,$connect) ;

              $sQuery = "DELETE FROM kofic_fix_theather
                               WHERE Code IN (
                                                  SELECT b.Code
                                                    FROM kofic_theather     a
                                              RIGHT JOIN kofic_fix_theather b
                                                      ON a.Code = b.Code
                                                   WHERE a.Code IS NULL
                                             )
                        ";
              mysql_query($sQuery,$connect) ;

              Delete_WrkJob($Jobing,$connect);
        }

        // B : "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do";
        //
        //

        $Jobing = "B";

        if  ($JobCode == $Jobing)
        {
            $count = 0 ;

            $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
            $QryKoficTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
            {
                $sTheatherCode = $ArrKoficTheather["Code"] ;
                $sTheatherName = $ArrKoficTheather["TheatherName"] ;

                $percent = round(($count++ / $cntTeather) * 99.0) ;
                Update_WrkJob($Jobing,$sTheatherName,$percent,$connect);

                //$sTheatherName_ = iconv("EUC-KR", "UTF-8",$sTheatherName);

                //if  ($sTheatherCode == "002127")
                //{
                $ShowDate = date("Ymd",time()) ; // 오늘 ...

                for ($day=1 ; $day<=7 ; $day++) // 일주일치를 다 땡긴다...
                {
                    ///echo "<br>".$ShowDate."-----------------------------------------------------------------------------";

                    $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do";
                    $vars = array(
                               "showDt"  => $ShowDate,
                               "theaCd"  => $sTheatherCode
                               );
                    $retValue = get_remotefile($url,$vars);
                    $retValue = trim($retValue);
                    $retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
                    $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환
                    //echo "<br>".$retValue."<br><br>";

                    $json = new Services_JSON();        // create a new instance of Services_JSON
                    $jvalue = $json->decode($retValue); // json형태의 스트링을 php 배열로 변환한다.
                    //echo "<br>".$jvalue ; // 지우지 말것 ..

                    //$theather = $jvalue->{'theater'} ; // 극장정보 - 필요없음
                    $schedule = $jvalue->{'schedule'}; // 상영스케줄 정보
                    /*
                    $size = sizeof($theather) ;
                    for ($i=0 ; $i<$size ; $i++)
                    {
                       foreach($theather[$i] as $key => $value)
                       {
                           //echo "<br>".$key.":".$value."<br>";
                       }
                    }
                    */

                    $size = sizeof($schedule) ;
                    for ($i=0 ; $i<$size ; $i++)
                    {
                         foreach($schedule[$i] as $key => $value)
                         {
                             //echo "<br>".$key.":".$value ;

                             if  ($key == "scrnNm")   { $scrnNm = iconv("UTF-8","EUC-KR",$value) ;   }
                             if  ($key == "movieCd")  { $movieCd = $value ;  }
                             if  ($key == "movieNm")  { $movieNm = iconv("UTF-8","EUC-KR",$value) ;  }
                             if  ($key == "showTm")
                             {
                                  $showTms = split(",", $value);

                                  $sQuery = "DELETE FROM kofic_showtime
                                                   WHERE TheatherCd  = '$sTheatherCode'
                                                         `Date`      = '$ShowDate'
                                                         ScrnNm      = '$scrnNm'
                                            " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                                  mysql_query($sQuery,$connect) ;

                                  $j = 1 ;
                                  foreach ($showTms as $showTm)
                                  {
                                       $sQuery = "INSERT INTO kofic_showtime
                                                       VALUES (
                                                               '$sTheatherCode'
                                                              ,'$ShowDate'
                                                              ,'$scrnNm'
                                                              ,$j
                                                              ,'$showTm'
                                                              ,'$movieCd'
                                                              ,'$movieNm'
                                                              )
                                                 " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                                       mysql_query($sQuery,$connect) ;

                                       $j ++;
                                  }
                             }
                         }

                         $sQuery = "DELETE FROM kofic_movie
                                          WHERE Code = '$movieCd'
                                   " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                         mysql_query($sQuery,$connect) ;

                         $sQuery = "INSERT INTO kofic_movie
                                         VALUES (
                                                 '$movieCd'
                                                ,'$movieNm'
                                                )
                                   " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                         mysql_query($sQuery,$connect) ;
                    }


                    $ShowDate = date("Ymd",strtotime("$day Day",time())) ;

                }
                //}
          }
          Delete_WrkJob($Jobing,$connect);
        }

        // C : "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterCodeLayer.do?theaCd=".$sTheatherCode;
        //
        //

        $Jobing = "C";

        if  ($JobCode == $Jobing)
        {
            $count = 0 ;

            $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
            $QryKoficTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
            {
                $sTheatherCode = $ArrKoficTheather["Code"] ;
                $sTheatherName = $ArrKoficTheather["TheatherName"] ;

                $percent = round(($count++ / $cntTeather) * 99.0) ;
                Update_WrkJob($Jobing,$sTheatherName,$percent,$connect);

                $swScrnCd = false ;
                $swScrnNm = false ;
                $swSeat   = false ;

                $arrScrnCd = array();
                $arrScrnNm = array();
                $arrSeat   = array();

                $vars = array();
                $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterCodeLayer.do?theaCd=".$sTheatherCode;
                $retValue = get_remotefile($url,$vars);
                //echo "<br>".$retValue;

                $arrLine2 = split("\n",$retValue); // 한줄씩 분리..
                for ($j=0;$j< sizeof($arrLine2);$j++)
                {
                    //echo  "<br>".$arrLine2[$j];

                    $trm = trim($arrLine2[$j]);

                    if  (strpos($trm, '&lt;th&gt;스크린코드&lt;/th&gt;') !== false)  {  $swScrnCd = true ; continue; }
                    if  (strpos($trm, '&lt;th&gt;스크린&lt;/th&gt;') !== false)  {  $swScrnNm = true ; continue; }
                    if  (strpos($trm, '&lt;th scope=&quot;row&quot; class=&quot;sty3 ct&quot;&gt;좌석수&lt;/th&gt;') !== false)  {  $swSeat   = true ; continue; }

                    if  (  (strpos($trm, '&lt;/colgroup&gt;') !== false)
                        || (strpos($trm, '&lt;/tr&gt;')       !== false))
                    {
                        $swScrnCd = false ;
                        $swScrnNm = false ;
                        $swSeat   = false ;
                    }

                    if  (($swScrnCd == true) && (strpos($trm, '&lt;th&gt;') !== false))
                    {
                        $sp = strpos($trm, '&lt;th&gt;') + 10;
                        $ep = strpos($trm, '&lt;/th&gt;');

                        $ScrnCd = substr($trm,$sp,$ep-$sp) ;

                        array_push($arrScrnCd , $ScrnCd);

                        continue ;
                    }

                    if  (($swScrnNm == true) && (strpos($trm, '&lt;th&gt;') !== false))
                    {
                        $sp = strpos($trm, '&lt;th&gt;') + 10;
                        $ep = strpos($trm, '&lt;/th&gt;');

                        $ScrnNm = substr($trm,$sp,$ep-$sp) ;

                        array_push($arrScrnNm , $ScrnNm);

                        continue ;
                    }

                    if  (($swSeat == true) && (strpos($trm, '&lt;td class=&quot;ct&quot;&gt;') !== false))
                    {
                        $sp = strpos($trm, '&lt;td class=&quot;ct&quot;&gt;') + 31;
                        $ep = strpos($trm, '&lt;/td&gt;');

                        $Seat = substr($trm,$sp,$ep-$sp) ;

                        array_push($arrSeat , $Seat);

                        continue ;
                    }
                    //echo "<br>"."[".$ScrnCd."]"."[".$ScrnNm."]"."[".$Seat."] ";
                }

                for($k=0;$k< count($arrScrnCd);$k++)
                {
                    //echo "<br>".$arrScrnCd[$k]."/".$arrScrnNm[$k]."/". (int)$arrSeat[$k];
                    $ScrnNm = iconv("UTF-8", "EUC-KR",$arrScrnNm[$k]);  // 한글처리

                    $sQuery = "INSERT INTO kofic_screen
                                    VALUES (
                                            '".$sTheatherCode."'
                                           ,'".$arrScrnCd[$k]."'
                                           ,'".$ScrnNm."'
                                           ,".(int)$arrSeat[$k]."
                                           )
                              " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                    mysql_query($sQuery,$connect) ;
                }
            }

            $sQuery = "UPDATE kofic_theather tht
                          SET Seat =  (
                                          SELECT sum( Seat )
                                           FROM kofic_screen
                                          WHERE TheatherCd = tht.Code
                                      )
                      " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
            mysql_query($sQuery,$connect) ;

            Delete_WrkJob($Jobing,$connect);
        }



        // E : "http://www.kobis.or.kr/kobis/business/mast/thea/findShowHistory.do";
        //
        //

        $Jobing = "E";

        if  ($JobCode == $Jobing)
        {
            $count = 0 ;

            $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 극장리스트를 구한다.
            $QryKoficTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
            {
                $sTheatherCode = $ArrKoficTheather["Code"] ;
                $sTheatherName = $ArrKoficTheather["TheatherName"] ;

                $percent = round(($count++ / $cntTeather) * 99.0) ;
                Update_WrkJob($Jobing,$sTheatherName,$percent,$connect);

                $StShowDate = date("Y-m-d",strtotime("-1 Day",time())) ;
                $EtShowDate = date("Y-m-d",strtotime("6 Day",time())) ;
          //$EtShowDate = "2015-09-30" ;
          //$StShowDate = "2015-09-30" ;

                $url = "http://www.kobis.or.kr/kobis/business/mast/thea/findShowHistory.do";
                $vars = array(
                             "theaCd"      => "$sTheatherCode",
                             "theaArea"    => "Y",
                             "showStartDt" => "$StShowDate",
                             "showEndDt"   => "$EtShowDate",
                             "sWideareaCd" => "",
                             "sBasareaCd"  => "",
                             "sTheaCd"     => "",
                             "choice"      => "2",
                             "sTheaNm"     => "$sTheatherName"
                             );
                $retValue = get_remotefile($url,$vars);
                //$retValue = trim($retValue);
                //$retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
                $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환
         //echo "<br>".$retValue;

                $findShowRoom = false;
                $firstShowRoom = false;
                $CntShowRoom = 0;

                $ShowRooms = array();

                $findInning = false;
                $Innings   = array();

                $offsetShowRoom = -1;
                $nInning = 11 ;

                $arrLine1 = split("\n",$retValue); // 한줄씩 분리..
                for ($i=0;$i< sizeof($arrLine1);$i++)
                {
                    $line1 = trim($arrLine1[$i]) ;
         //echo "<br>".htmlspecialchars($line1) ;
                    if  (strpos($line1, "<th scope=\"col\">상영관</th>")!==false)
                    {
                        if  ($firstShowRoom == true) continue ;
                        //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+2]))."]" ;
                        //$tmp = trim($arrLine1[$i+2]) ;
                        //$Date = substr($tmp,4,4).substr($tmp,12,2).substr($tmp,18,2) ;
                        //echo "<br>"."[".$line1."]" ;

                        $findShowRoom = true;
                        $firstShowRoom = true;

                        continue;
                    }
                    if  (strpos($line1, "<th scope=\"col\">총")!==false)
                    {
                        $findShowRoom = false;

                        continue;
                    }
                    if  (strpos($line1, "<th scope=\"col\">")!==false)
                    {
                        if  ($findShowRoom == true)
                        {
                            //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+1]))."]" ;

                            array_push($ShowRooms, trim($arrLine1[$i+1]));
                            $CntShowRoom ++ ;
                        }
                        //$tmp = trim($arrLine1[$i+2]) ;
                        //$Date = substr($tmp,4,4).substr($tmp,12,2).substr($tmp,18,2) ;
                    }

                    if  (strpos($line1, "<th>좌석수</th>")!==false)
                    {
                         for ($j=1 ; $j<=$CntShowRoom ; $j++)
                         {
                             //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+($j*2)]))."]" ;
                             $sp = strpos($arrLine1[$i+($j*2)], "<td>") + 4;
                             $ep = strpos($arrLine1[$i+($j*2)], "</td>");
                             $Seat = substr($arrLine1[$i+($j*2)],$sp,$ep-$sp);

                             $ScrnNm = iconv("UTF-8", "EUC-KR",$ShowRooms[$j-1]);  // 한글처리

                             $sQuery = "DELETE FROM kofic_seat
                                              WHERE TheatherCd = '$sTheatherCode'
                                                    ScrnNm     = '$ScrnNm'
                                       " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                             mysql_query($sQuery,$connect) ;

                             $sQuery = "INSERT INTO kofic_seat
                                             VALUES (
                                                     '$sTheatherCode'
                                                    ,'$ScrnNm'
                                                    ,$Seat
                                                    )
                                       " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                             mysql_query($sQuery,$connect) ;
                         }
                    }

                    if  (strpos($line1, "<th scope=\"col\">상영일자</th>")!==false)
                    {
                        $findInning = true ;

                        continue;
                    }
                    if  (strpos($line1, "<th scope=\"col\">")!==false)
                    {
                        if  ($findInning == true)
                        {
                            $sp = strpos($line1, "<th scope=\"col\">") + 16;
                            $ep = strpos($line1, "회</th>");
                            $Inning = substr($line1,$sp,$ep-$sp);

                            array_push($Innings, $Inning);

                            //echo "[".$Inning."]" ;
                        }
                    }
                    if  (strpos($line1, "</tr>")!==false)
                    {
                        if  ($findInning == true) $findInning = true;
                    }

                    if  ((strpos($line1, "<tr >")!==false) || (strpos($line1, "<tr class='last-child'>")!==false))
                    {
                        if  (strpos($arrLine1[$i+1],"<td rowspan='")!==false)
                        {
                            $offsetShowRoom  = 2;

                            $sp = strpos($arrLine1[$i+1],"</td>") - 10;
                            $temp =  substr($arrLine1[$i+1],$sp,10);

                            $baseDate = substr($temp,0,4) . substr($temp,5,2) . substr($temp,8,2) ;

                            //echo "<br>"."---------------------------[".$baseDate."]--------------------------" ;
                        }
                        else
                        {
                            $offsetShowRoom  = 2;
                        }
                        $sp = strpos($arrLine1[$i+$offsetShowRoom], "<td>") + 4;
                        $ep = strpos($arrLine1[$i+$offsetShowRoom], "</td>");
                        $curShowroom = substr($arrLine1[$i+$offsetShowRoom],$sp,$ep-$sp);

                        //echo "<br>"."[".$curShowroom."]" ;

                        $nInning = 0 ;

                        $curShowroom = iconv("UTF-8", "EUC-KR",$curShowroom);  // 한글처리
                    }
                    if  (strpos($line1, "<td class=\"left\">")!==false) // 발견되고..
                    {
                        if  (strpos($arrLine1[$i+1], "</td>")===false) // 발견되지 않으면..
                        {
                            if  (strpos($arrLine1[$i+1], "<font color=\"red\">")!==false) // 발견되고..(가격이 없음)
                            {
                                //$time    = substr($arrLine1[$i+2],0,2) . substr($arrLine1[$i+2],3,2) ;
                                $tmp       = trim($arrLine1[$i+2]) ;
                                $time      = substr($tmp,0,2) .substr($tmp,3,2) ;
                                $unitprice = "0";
                                $movieNm   = trim($arrLine1[$i+3]);
                            }
                            else
                            {
                                $ep = strpos($arrLine1[$i+1], "(");
                                $time = substr($arrLine1[$i+1],$ep-6,2) . substr($arrLine1[$i+1],$ep-3,2) ;

                                $sp = strpos($arrLine1[$i+1], "(") + 1;
                                $ep = strpos($arrLine1[$i+1], "원)<br>");
                                $unitprice =  str_replace(",", "", substr($arrLine1[$i+1],$sp,$ep-$sp));

                                $movieNm = trim($arrLine1[$i+2]);
                            }

                            $Inning =  $Innings[$nInning];

                            //$ep = strpos($movieNm, "(");
                            //$movieNm = substr($movieNm,0,$ep) ;

                            $movieNm_ = iconv("UTF-8", "EUC-KR",$movieNm);  // 한글처리

                            $sQuery = "SELECT * FROM kofic_movie
                                        WHERE MovieName = '$movieNm_'
                                      " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            $QryMovieName = mysql_query($sQuery,$connect) ;
                            if  ($ArrMovieName = mysql_fetch_array($QryMovieName))
                            {
                                $movieCd = $ArrMovieName["Code"];
                            }
                            else
                            {
                                $movieCd = "";
                            }

                            $sQuery = "INSERT INTO kofic_playing
                                            VALUES (
                                                    '$sTheatherCode'
                                                   ,'$baseDate'
                                                   ,'$curShowroom'
                                                   ,$Inning
                                                   ,'$time'
                                                   ,'$movieCd'
                                                   ,'$movieNm_'
                                                   ,$unitprice
                                                   )
                                      " ;//echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                            mysql_query($sQuery,$connect) ;

                            // [회차], [시작시간], [단가], [영화코드], [영화명]
                            //echo "<br>"."[".$Inning."][".$time."][".$unitprice."][".$movieCd."][".$movieNm."]" ;
                        }

                        $nInning ++ ;
                    }

                }
            }
            Delete_WrkJob($Jobing,$connect);
        }
    }

    sleep(1);

    Delete_WrkJob_All($connect);

    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>