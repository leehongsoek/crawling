  <?   // "http://www.kobis.or.kr/kobis/business/mast/thea/findTheaterInfoList.do"  // 영화상영관 정보
       set_time_limit(0) ;

       include "inc/config.php";       // {[데이터 베이스]} : 환경설정

       $connect = dbconn() ;           // {[데이터 베이스]} : 연결

       mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택
  ?>
  <html lang="en">
      <head>
  <? include "inc/Head.inc"; ?>


          <script type="text/javascript">
          function active_css()
          {
              $('#menu1').attr("class","active has-sub");
          };
          </script>


          <title>상영관리스트 가져오기</title>
      </head>
      <body>
  <? include "inc/Menu.inc"; ?>

  <?
      include "lib/get_remotefile.php";

      $exit = false ;

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

        $pattern = "\n";

        $arrLine1 = split($pattern,$retValue); // 한줄씩 분리..
        for ($i=0 ; $i< sizeof($arrLine1) ; $i++)
        {
            // 극장명 캐치
            if  (strpos($arrLine1[$i], "javascript:fn_detail(event, $(this),")!==false)
            {
                $trm = trim($arrLine1[$i]);

                $TheatherCode = substr($trm,116,6) ;

                $sp = strpos($trm, "return false;&quot;&gt;") + 23;
                $ep = strpos($trm, "&lt;/a&gt;&lt;/td&gt;");

                $TheatherName = substr($trm,$sp,$ep-$sp) ;

                echo "<br>".$TheatherCode.", ".$TheatherName; // 극장 코드, 극장명

                $TheatherName = iconv("UTF-8", "EUC-KR",$TheatherName);  // 한글처리

                $sQuery = "DELETE FROM kofic_theather
                                 WHERE Code = '".$TheatherCode."'
                          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                mysql_query($sQuery,$connect) ;

                $sQuery = "INSERT INTO kofic_theather
                                VALUES ('".$TheatherCode."'
                                       ,'".$TheatherName."'
                                       ,( SELECT IFNULL(SUM(Seat),0)
                                            FROM kofic_seat
                                           WHERE TheatherCd = '".$TheatherCode."' )
                                       )
                          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                mysql_query($sQuery,$connect) ;

                $sQuery = "UPDATE kofic_fix_theather
                              SET TheatherName = '".$TheatherName."'
                            WHERE Code = '".$TheatherCode."'
                          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
                mysql_query($sQuery,$connect) ;
            }

            if  (strpos($arrLine1[$i], "검색된 데이터가 존재하지 않습니다.")!==false)
            {
                $exit = true ;
                break;
            }
        }
        if($exit == true) break;
      }

      $sQuery = "UPDATE kofic_theather tht
                    SET Seat =  (SELECT SUM(Seat)
                                   FROM kofic_seat
                                  WHERE TheatherCd = tht.Code
                                )
                " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;
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

      $sQuery = "DELETE FROM kofic_showroom
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

  ?>

      </body>
  </html>

  <?
      mysql_close($connect) ;      // {[데이터 베이스]} : 단절
  ?>

