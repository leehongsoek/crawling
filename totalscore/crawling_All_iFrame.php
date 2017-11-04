<?
    header("Pragma: no-cache");
    header("Cache-Control: no-cache,must-revalidate");

    require('FirePHPCore/FirePHP.class.php');
    ob_start();

    $firephp = FirePHP::getInstance(true);

    set_time_limit(0) ;

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정
    $connect = dbconn() ;           // {[데이터 베이스]} : 연결
    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    include "lib/get_remotefile.php"; // 클로링 라이브러리
    include "lib/JSON.php";           // json 리더 라이브러리

    function stopTimer()
    {
        //ob_clean();

        echo "<script type=\"text/javascript\">";
        echo "if (self!=top) parent.StopTimer();"; // 자기 자신이 ifram안에 있을때만 호출한다.
        echo "</script>";

        ob_flush();
    };

    function sendMessageToParent($_Type,$_Msg,$_Percent)
    {
        //ob_clean();

        echo "<script type=\"text/javascript\">";
        echo "if (self!=top) parent.Message('$_Type|$_Msg|$_Percent');"; // 자기 자신이 ifram안에 있을때만 호출한다.
        echo "</script>";

        ob_flush();
    };
?>
<!DOCTYPE html>
<html lang="kr">
<body>
Reading!!<br>
<?
    $JOBS = array("A","B","C","D","E");
    //$JOBS = array("B");  // 부분적으로 실행할때(디버깅용)

    $cntTeather = 405;

    //////                                                                                 //
    /////                                                                                 ///
    ////  A : "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterInfoList.do"   ////
    ///                                                                                 /////
    //                                                                                 //////

    $Jobing = "A";

    if  (in_array($Jobing, $JOBS))
    {
        $exit = false ;

        $cntTeather = 0 ;

		$no = 0;

        for ($p=1;;$p++) //43 페이지를 끝까지 돈다. - 페이지가 계속늘어남...
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
                $line1 = trim($arrLine1[$i]) ; //echo "<br>".$arrLine1[$i];

                // 극장명 캐치
                if  (strpos($line1, "javascript:fn_detail(event, $(this),")!==false)
                {
					$no ++;

                    $TheatherCode = preg_replace("/[^0-9]*/s", "", substr($line1,116,6)); //  숫자만 취한다. 가끔5자리코드가 나올수 있으므로 ....

                    $sp = strpos($line1, "return false;&quot;&gt;") + 23;
                    $ep = strpos($line1, "&lt;/a&gt;&lt;/td&gt;");

                    $TheatherName = substr($line1,$sp,$ep-$sp) ;
                    //echo "<br>".$TheatherCode.", ".$TheatherName; $firephp->fb($p." Page - ".$TheatherName,FirePHP::WARN);   // 극장 코드, 극장명

                    $TheatherName_ = iconv("UTF-8","EUC-KR",$TheatherName);  // 한글처리

                    $sQuery = "DELETE FROM kofic_theather
                                     WHERE Code = '".$TheatherCode."'
                              " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery);
                    mysql_query($sQuery,$connect) ;

                    $sQuery = "INSERT INTO kofic_theather
                                    VALUES ('".$TheatherCode."'
                                           ,'".$TheatherName_."'
                                           ,( SELECT IFNULL(SUM(Seat),0)
                                                FROM kofic_seat
                                               WHERE TheatherCd = '".$TheatherCode."' )
                                           )
                              " ; //echo "<br><br>".$p." Page - ".iconv("EUC-KR","UTF-8",$sQuery);
                    mysql_query($sQuery,$connect) ;


                    $sQuery = "INSERT INTO kofic_fix_theather
                                    VALUES ('".$TheatherCode."'
                                           ,''
                                           ,'".$TheatherName_."'
                                           ,null
                                           ,null
                                           )
                              " ; //echo "<br><br>".$p." Page - ".iconv("EUC-KR","UTF-8",$sQuery);
                    mysql_query($sQuery,$connect) ;


                    sendMessageToParent($Jobing,$TheatherName,"0");

                    $sQuery = "UPDATE kofic_fix_theather
                                  SET TheatherName = '".$TheatherName_."'
                                WHERE Code = '".$TheatherCode."'
                              " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery);
                    mysql_query($sQuery,$connect) ;

                    $cntTeather++;

                    //echo  "<br>".iconv("EUC-KR","UTF-8",$TheatherName);
					//echo  "<br>".$TheatherName;
                }

                if  (strpos($arrLine1[$i], "검색된 데이터가 존재하지 않습니다.")!==false)
                {
                    $exit = true ;
                    break;
                }
            }

			//echo  "<br>".$no;

            if($exit == true) break;
        }

        // 좌석테이블에 해당극장의 좌석수가 있으면 좌석수를 가지고 온다.
        $sQuery = "UPDATE kofic_theather tht
                      SET Seat =  (SELECT IFNULL(SUM(Seat),0)
                                     FROM kofic_seat
                                    WHERE TheatherCd = tht.Code
                                  )
                  " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery);
        mysql_query($sQuery,$connect) ;

        // 영진위에서 삭제된 극장(code가 null)은 관계되는 좌석 정보도 같이 지운도록 처리 ..
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

        // 영진위에서 삭제된 극장(code가 null)은 관계되는 상영 정보도 같이 지운도록 처리 ..
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

        // 영진위에서 삭제된 극장(code가 null)은 관계되는 고정극장 정보도 같이 지운도록 처리 ..
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

        echo "<br><br>A 완료!!";
    }

    //////                                                                          //
    /////                                                                          ///
    ////  B : "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do"   ////
    ///                                                                          /////
    //                                                                          //////

    $Jobing = "B";

    if  (in_array($Jobing, $JOBS))
    {
        $count = 0 ;

        $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery);   // 극장리스트를 구한다.
        $QryKoficTheather = mysql_query($sQuery,$connect) ;
        while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
        {
            $sTheatherCode = $ArrKoficTheather["Code"] ;
            $sTheatherName = $ArrKoficTheather["TheatherName"] ;

            $percent = round(($count++ / $cntTeather) * 99.0) ;

            sendMessageToParent($Jobing,iconv("EUC-KR","UTF-8",$sTheatherName),$percent); // 상태 : 극장출력

            //$sTheatherName_ = iconv("EUC-KR","UTF-8",$sTheatherName);

            //if  ($sTheatherCode == "002127")
            //{
            $ShowDate = date("Ymd",time()) ; // 오늘 ...

            for ($day=1 ; $day<=7 ; $day++) // 일주일치를 다 땡긴다...
            {
                ///echo "<br>".$ShowDate."-----------------------------------------------------------------------------";

                sendMessageToParent($Jobing."_".$day,iconv("EUC-KR","UTF-8",$sTheatherName)." : ".substr($ShowDate, 0, 4)."/".substr($ShowDate, 4, 2)."/".substr($ShowDate, 6, 2),$percent); // 상태 : 극장별 날짜별 출력(스케쥴)

				$exloop = true ;

				for(;$exloop == true;)
				try
				{
					$url = "http://www.kobis.or.kr/kobis/business/mast/thea/findSchedule.do";
					$vars = array(
							   "showDt"  => $ShowDate,
							   "theaCd"  => $sTheatherCode
							   );
					$retValue = get_remotefile($url,$vars);

					$exloop = false ;
				}
				catch (Exception $e)
				{
					echo '에러발생: ',  $e->getMessage(), "<br>";
					$exloop = true ;
				}
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
                                        " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
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
                                             " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                                   mysql_query($sQuery,$connect) ;

                                   $j ++;
                              }
                         }
                     }

                     // 영화명을 코드로 갱신..
                     $sQuery = "DELETE FROM kofic_movie
                                      WHERE Code = '$movieCd'
                               " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                     mysql_query($sQuery,$connect) ;

                     $sQuery = "INSERT INTO kofic_movie
                                     VALUES (
                                             '$movieCd'
                                            ,'$movieNm'
                                            )
                               " ;  //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                     mysql_query($sQuery,$connect) ;
                }


                $ShowDate = date("Ymd",strtotime("$day Day",time())) ; // 날짜 증가...

            }
            //}
        }

        echo "<br><br>B 완료!!";
    }

    //////                                                                                                          //
    /////                                                                                                          ///
    ////  C : "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterCodeLayer.do?theaCd=".$sTheatherCode    ////
    ///                                                                                                          /////
    //                                                                                                          //////

    $Jobing = "C";

    if  (in_array($Jobing, $JOBS))
    {
        $count = 0 ;

        $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;   // 극장리스트를 구한다.
        $QryKoficTheather = mysql_query($sQuery,$connect) ;
        while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
        {
            $sTheatherCode = $ArrKoficTheather["Code"] ;
            $sTheatherName = $ArrKoficTheather["TheatherName"] ;

            $percent = round(($count++ / $cntTeather) * 99.0) ;

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

            sendMessageToParent($Jobing,iconv("EUC-KR","UTF-8",$sTheatherName)." (".count($arrScrnCd).")",$percent); // 상태 :  극장별 스크린수

            for($k=0;$k< count($arrScrnCd);$k++)
            {
                //echo "<br>".$arrScrnCd[$k]."/".$arrScrnNm[$k]."/". (int)$arrSeat[$k];
                $ScrnNm = iconv("UTF-8","EUC-KR",$arrScrnNm[$k]);  // 한글처리

                $sQuery = "INSERT INTO kofic_screen
                                VALUES (
                                        '".$sTheatherCode."'
                                       ,'".$arrScrnCd[$k]."'
                                       ,'".$ScrnNm."'
                                       ,".(int)$arrSeat[$k]."
                                       )
                          " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                mysql_query($sQuery,$connect) ;
            }
        }

        $sQuery = "UPDATE kofic_theather tht
                      SET Seat =  (
                                      SELECT sum( Seat )
                                       FROM kofic_screen
                                      WHERE TheatherCd = tht.Code
                                  )
                  " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
        mysql_query($sQuery,$connect) ;

        echo "<br><br>C 완료!!";
    }

    //////                                                                                     //
    /////                                                                                     ///
    ////  D : "http://www.kobis.or.kr/kobis/business/stat/boxs/findDailyBoxOfficeList.do"    ////
    ///                                                                                     /////
    //                                                                                     //////

    $Jobing = "D";

    if  (in_array($Jobing, $JOBS))
    {
        $vars = array();

        $StShowDate = date("Y-m-d",strtotime("-7 Day",time())) ;
        $EtShowDate = date("Y-m-d",strtotime("-1 Day",time())) ;

        $sQuery = "DELETE FROM kofic_boxoffice
                         WHERE `Date` >= '$StShowDate'
                           AND `Date` <= '$EtShowDate'
                  " ; // echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
        mysql_query($sQuery,$connect) ;

        $url = "http://www.kobis.or.kr/kobis/business/stat/boxs/findDailyBoxOfficeList.do"
              ."?loadEnd=0"
              ."&searchType=search"
              ."&sSearchFrom=$StShowDate"
              ."&sSearchTo=$EtShowDate"
              ."&sMultiMovieYn="
              ."&sRepNationCd="
              ."&sWideAreaCd=";
        $retValue = get_remotefile($url,$vars);
        //$retValue = trim($retValue);
        //$retValue = preg_replace('/\r\n|\r|\n/','',$retValue); // 개행제거
        $retValue = htmlspecialchars_decode($retValue); // html변환문자를 정상문자로 전환

        $pattern = "\n";

        $arrLine1 = split($pattern,$retValue); // 한줄씩 분리..
        for ($i=0;$i< sizeof($arrLine1);$i++)
        {
            $line1 = trim($arrLine1[$i]) ;

            if  (strpos($line1, "<div class=\"board_tit\">")!==false)
            {
                //echo "<br>"."[".htmlspecialchars(trim($arrLine1[$i+2]))."]" ;
                $tmp = trim($arrLine1[$i+2]) ;
                $Date = substr($tmp,4,4).substr($tmp,12,2).substr($tmp,18,2) ;

                //echo "<br>"."/".$Date."/" ;
            }
            if  (strpos($line1, "<td title=\"")!==false)
            {
                $sp = 11 ;
                $ep = strpos($line1, "\">");

                $rank = substr($line1,$sp,$ep-$sp);
                //echo "<br/>"."[".htmlspecialchars($line1)."]" ;
                //echo "<br>"."[".$rank."]" ;

                //[<a href="#" class="boxMNm" onclick="mstView('movie','20148851');return false;" title="암살">]
            }
            if  (strpos($line1, "<a href=\"#\" class=\"boxMNm\" onclick=\"mstView('movie','")!==false)
            {
                $sp = 53 ;
                $movieCd = substr($line1,$sp,8) ;
                //echo "<br>"."[".$movieCd."]" ;
                //$ep = strpos($line1, "\">");
            }

            if  (strpos($line1, "');return false;\" title=\"")!==false)
            {
                $sp = strpos($line1, "');return false;\" title=\"") + 25;
                $ep = strpos($line1, "\">");
                $movieNm = substr($line1,$sp,$ep-$sp);

                //echo "<br>"."[".$movieNm."]" ;
                //echo "<br>"."[".$rank."]"."[".$movieCd."]"."[".$movieNm."]" ;

                $movieNm_ = iconv("UTF-8","EUC-KR",$movieNm);  // 한글처리
                $sQuery = "INSERT INTO kofic_boxoffice
                                VALUES (
                                        '$Date'
                                       ,$rank
                                       ,'$movieCd'
                                       ,'$movieNm_'
                                       )
                          " ; // echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                mysql_query($sQuery,$connect) ;
            }
        }

        echo "<br><br>D 완료!!";
    }

    //////                                                                              //
    /////                                                                              ///
    ////  E : "http://www.kobis.or.kr/kobis/business/mast/thea/findShowHistory.do"    ////
    ///                                                                              /////
    //                                                                              //////

    $Jobing = "E";

    if  (in_array($Jobing, $JOBS))
    {
        $count = 0 ;

        $sQuery = "SELECT * FROM kofic_theather  " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;   // 극장리스트를 구한다.
        $QryKoficTheather = mysql_query($sQuery,$connect) ;
        while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
        {
            $sTheatherCode = $ArrKoficTheather["Code"] ;
            $sTheatherName = $ArrKoficTheather["TheatherName"] ;

            $percent = round(($count++ / $cntTeather) * 99.0) ;

            sendMessageToParent($Jobing,iconv("EUC-KR","UTF-8",$sTheatherName),$percent);

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

                         $ScrnNm = iconv("UTF-8","EUC-KR",$ShowRooms[$j-1]);  // 한글처리

                         $sQuery = "DELETE FROM kofic_seat
                                          WHERE TheatherCd = '$sTheatherCode'
                                                ScrnNm     = '$ScrnNm'
                                   " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                         mysql_query($sQuery,$connect) ;

                         $sQuery = "INSERT INTO kofic_seat
                                         VALUES (
                                                 '$sTheatherCode'
                                                ,'$ScrnNm'
                                                ,$Seat
                                                )
                                   " ;  //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
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

                    $curShowroom = iconv("UTF-8","EUC-KR",$curShowroom);  // 한글처리
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
                        $movieNm = str_replace("'", " ", $movieNm);

                        $Inning =  $Innings[$nInning];

                        //$ep = strpos($movieNm, "(");
                        //$movieNm = substr($movieNm,0,$ep) ;

                        $movieNm_ = iconv("UTF-8","EUC-KR",$movieNm);  // 한글처리

                        $sQuery = "SELECT * FROM kofic_movie
                                    WHERE MovieName = '$movieNm_'
                                  " ; //echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
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
                                  " ;//echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
                        mysql_query($sQuery,$connect) ;

                        // [회차], [시작시간], [단가], [영화코드], [영화명]
                        //echo "<br>"."[".$Inning."][".$time."][".$unitprice."][".$movieCd."][".$movieNm."]" ;
                    }

                    $nInning ++ ;
                }

            }
        }

        echo "<br><br>E 완료!!";
    }

    //////                                       //
    /////                                       ///
    ////  기준 삭제일자로 과거자료 삭제 ..     ////
    ///                                       /////
    //                                       //////

    $DelDate = date("Ymd",strtotime("-7 Day",time())) ; // 기준 삭제일자...

    $sQuery = "DELETE FROM kofic_boxoffice  WHERE Date < '$DelDate'  " ;//echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
    mysql_query($sQuery,$connect) ;

    $sQuery = "DELETE FROM kofic_fix_boxoffice  WHERE Date < '$DelDate'  " ;//echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
    mysql_query($sQuery,$connect) ;

    $sQuery = "DELETE FROM kofic_playing  WHERE Date < '$DelDate' " ;//echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
    mysql_query($sQuery,$connect) ;

    $sQuery = "DELETE FROM kofic_showtime  WHERE Date < '$DelDate' " ;//echo "<br>".iconv("EUC-KR","UTF-8",$sQuery); ;
    mysql_query($sQuery,$connect) ;


    //////                                       //
    /////                                       ///
    ////  완료!                                ////
    ///                                       /////
    //                                       //////

    sendMessageToParent(" ","완료!","100");

    //sleep(1);

    stopTimer();
?>

</body>
</html>
<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>