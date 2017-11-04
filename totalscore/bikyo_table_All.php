<?
    require('FirePHPCore/FirePHP.class.php');

    ob_start();

    $firephp = FirePHP::getInstance(true);

    session_start();
    set_time_limit(0) ;

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정
    $connect = dbconn() ;           // {[데이터 베이스]} : 연결
    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

    $Ranks    = $_GET['Ranks'];
    $PlayDate = $_GET['PlayDate'];
    $ToExel   = $_GET['ToExel'];


    if  ($ToExel=="Yes")
    {
        header( "Content-type: application/vnd.ms-excel;charset=utf-8");
        header( "Expires: 0" );
        header( "Cache-Control: must-revalidate, post-check=0,pre-check=0" );
        header( "Pragma: public" );
        header( "Content-Disposition: attachment; filename=name_".date('Ymd').".xls" );
    }

    include "inc/Library.php" ;

?>

<!DOCTYPE html>
<html lang="kr">
    <head>

        <?
        if  ($ToExel!="Yes") include "inc/Head.inc";
        ?>
        <script src="/amcharts/amcharts.js" type="text/javascript"></script>
        <script src="/amcharts/pie.js" type="text/javascript"></script>

        <script type="text/javascript">

        function active_css()
        {
            $('#menu2').attr("class","active has-sub");
        };

        $(function() {
          $( ".datepicker" ).datepicker({
            dateFormat: 'yy-mm-dd',
            prevText: '이전 달',
            nextText: '다음 달',
            monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
            monthNamesShort: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
            dayNames: ['일','월','화','수','목','금','토'],
            dayNamesShort: ['일','월','화','수','목','금','토'],
            dayNamesMin: ['일','월','화','수','목','금','토'],
            showMonthAfterYear: true,
            yearSuffix: '년'
          });
        });

        function datepicker_change()
        {
            frmMain.submit();
        }

        function start_click()
        {
            var select = 0;
            var ranks = "";

            $("input[name=rank]:checked").each(function()
            {
                select ++ ;

                if  (ranks != "") ranks += ",";

                ranks += $(this).val();
            });

            if  (select > 0)
            {
                $('input[name=Ranks]').val(ranks);
            }

            frmMain.submit();
        }

        //
        // 엑셀 출력
        //
        function toexel_click()
        {
           botttomaddr = "<?=$_SERVER['PHP_SELF']?>"
                       + "?ToExel=Yes"
                       + "&Ranks=<?=$Ranks?>"
                       + "&PlayDate=<?=$PlayDate?>"
           //alert(botttomaddr) ;
           location.href = botttomaddr ;
        }

        function chang_rank(baseDate,cur,objval)
        {
            var options = {
                            _Gubun  : '1',
                            _date   : baseDate,
                            _cur    : cur,
                            _objval : objval
                          } ;
            $.post("bikyo_table_ajax.php", options, function(data)
            {
                $('#display').text(data);

                if (data=="UPDATE")
                {
                    if  (cur > objval)
                    {
                        for (i=cur;i>objval;i--)
                        {
                            changeTD(i,i-1) ;
                        }
                    }
                    if  (cur < objval)
                    {
                        for (i=cur;i<objval;i++)
                        {
                            changeTD(i,i+1) ;
                        }
                    }
                }
            });
        }
        function changeTD(a1,a2)
        {
            var $elem1 = $("#td"+a1);
            var $elem2 = $("#td"+a2);
            var $placeholder = $("<td></td>");

            $elem2.after($placeholder);
            $elem1.after($elem2);

            $placeholder.replaceWith($elem1);


            $elem1.attr("id","td"+a2);
            $elem2.attr("id","td"+a1);
        }
        function up_click(baseDate,cur)
        {
            if  (cur==1)
            {
                alert("더이상 이동할 수 없습니다.");
            }
            else
            {
                chang_rank(baseDate,cur,cur-1) ;
            }
        }
        function dn_click(baseDate,cur,end)
        {
            if  (cur==end)
            {
                alert("더이상 이동할 수 없습니다.");
            }
            else
            {
                chang_rank(baseDate,cur,cur+1) ;
            }
        }
        function mv_click(baseDate,cur,end)
        {
            var objval = $("#txt"+cur).val();

            if  (objval == cur) return; // 변경이 없는 경우..

            if  ((objval<1) || (objval>end))
            {
                alert("더이상 이동할 수 없습니다."); // 범위 초과
            }
            else
            {
                chang_rank(baseDate,cur,objval) ;
            }
            $("#txt"+cur).val(cur);
        }

        // 좌석수가 없는 상영관에 자석수 입력을 위한 팝업창을 띄운다..
        function setSeat(TheatherCd,ScrnNm)
        {
            var url='popup_setSeat.php?TheatherCd='+TheatherCd+'&ScrnNm='+ScrnNm;

            cw=screen.availWidth;     //화면 넓이
            ch=screen.availHeight;    //화면 높이

            sw=100;    //띄울 창의 넓이
            sh=60;    //띄울 창의 높이

            ml=(cw-sw)/2;        //가운데 띄우기위한 창의 x위치
            mt=(ch-sh)/2;         //가운데 띄우기위한 창의 y위치

            window.open(url,'_blank','width='+sw+',height='+sh+',top='+mt+',left='+ml+',resizable=no,scrollbars=yes');


            //window.open(url,'_blank', 'menubar=no,location=no,scrollbars=no,width=100,height=60,status=no,resizable=no,top=0,left=0,dependent=no,alwaysRaised=no');
        }

		$(document).ready(function()
		{
			$(".edit_seat").keypress(function()
			{
				console.log(event.which);

				if (event.which && (event.which  > 47 && event.which  < 58 || event.which == 8))
				{

				}
				else
				{
					if (event.which == 13)
					{
						$.post("popup_setSeat_ajax.php"
							   ,{ theatherCd  : $(this).attr('theatherCd')
								 ,scrnNm      : $(this).attr('scrnNm')
								 ,seat        : this.value
								}
							   ,function(data)
								{
									if (data == "UPDATE")  alert("갱신이 완료되었습니다."); //
									if (data == "INSERT")  alert("추가가 완료되었습니다."); //
								});
					}

				}
			});
		});




        </script>

        <title>경쟁영화 회차 비교표 ALL</title>
    </head>
    <body>
<?
    if  (!session_is_registered("logged_UserId"))
    {
        ?>
        로그인을 해주세요!
        <?
    }
    else
    {
        if  ($ToExel!="Yes")
        {
            include "inc/Menu.inc";
            ?>
            <form name="frmMain" method="get" action="<?=$_SERVER['PHP_SELF']?>" >
                <input type="hidden" name="Ranks" />
                <br>
                년월일: <input type="text" name="PlayDate" class="datepicker" value="<?=$PlayDate?>" onchange="datepicker_change()">
                <button name="start" onclick="start_click()">집계</button>
                <?
                if  ($Ranks != null)
                {
                ?><a href=# onclick="toexel_click();"><img src="../mtnscokr/exel.gif" width="32" height="32" border="0"></a><?
                }
                ?>
                <br>
            </form>
            <br>
            <?
        }
        else
        {
            echo "경쟁영화 회차 비교표(ALL)<br>" ;
            echo $PlayDate ;
        }

        $PlayDate = substr($PlayDate,0,4) . substr($PlayDate,5,2) . substr($PlayDate,8,2); // 2016-06-06 -> 20160606
        $MaxDate  = Get_MaxDate_BoxOffice($connect) ;

        if  ($PlayDate >= $MaxDate) $BaseDate = $MaxDate ;  //  박스오피스 가장최근일자보다 뒤에 날짜를 조회하고자한다면..  -> 박스오피스 가장최근일자
        else                        $BaseDate = $PlayDate ; //  박스오피스 가장최근일자보다 앞에 날짜를 조회한다면.. -> 그대로 그 날짜로...

        if  ($Ranks == null) // 영화정보가 없는 경우.. 기본화면..
        {
            Make_Fix_Boxoffice($BaseDate,$connect) ;
            ?>
            <div class="t3">
              <table border="1">
              <tr>
                  <th colspan="3">BoxOffice최근일:<?=$MaxDate?></th>
              </tr>
              <tr>
                  <th>순위</th>
                  <th>영화명</th>
                  <th></th>
              </tr>
              <?
              // 박스오피스 리스트를 구한다. (수정도 가능하도록 한다.)
              $sQuery = "     SELECT *
                               FROM kofic_fix_boxoffice
                              WHERE `Date` = '$BaseDate'
                           ORDER BY Rank
                        " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
              $QryBoxOffice = mysql_query($sQuery,$connect) ; $num_rows = mysql_num_rows($QryBoxOffice);
              while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
              {
                  $Rank     = $ArrBoxOffice['Rank'];
                  $MovieCd  = $ArrBoxOffice['MovieCd'];
                  $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
                  $MovieName = $Rank.". ".$MovieNm;//."(".$MovieCd.")"
                  ?>
                  <tr>
                      <td class="ty2"><?=$Rank?></td>
                      <td id="td<?=$Rank?>"><label><input name="rank" id="Group" value="<?=$Rank?>" type="checkbox" />&nbsp;<?=$MovieNm?></label></td>
                      <td>

                          <button name="start" onclick="up_click('<?=$BaseDate?>',<?=$Rank?>)">↑</button>
                          <button name="start" onclick="dn_click('<?=$BaseDate?>',<?=$Rank?>,<?=$num_rows?>)">↓</button>

                          <input id="txt<?=$Rank?>" type="text" style="text-align:right;width: 40px;" size="3" value="<?=$Rank?>">
                          <button name="start" onclick="mv_click('<?=$BaseDate?>',<?=$Rank?>,<?=$num_rows?>)">이동</button>

                      </td>
                  </tr>
                  <?
                  //echo "<br>".$MovieNm."(".$MovieCd.")";
              }
              ?>
              </table>
            </div>

            <table width="90%">
            <tr>
                  <td><div id="display"></div></td>
            </tr>
            </table>

            <?
        }
        else  // 선택된 영화 랭킹자료가 있을 때....
        {
            ?>
            <div>집계기준일:<?=$PlayDate?></div>
            <?
            echo "<br>";


            //////                             //
            /////                             ///
            ////  경쟁영화 회차 비교표 1     ////
            ///                             /////
            //                             //////



            ?>

            <div class="t3">
            <table border="1">
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">극장명</th>
                <?
                $arrRank = split(",",$Ranks);
                for ($i=0;$i< sizeof($arrRank);$i++)
                {
                    $sQuery = "SELECT Rank
                                     ,MovieCd
                                     ,MovieNm
                                 FROM kofic_fix_boxoffice
                                WHERE `Date` = '$BaseDate'
                                  AND Rank = $arrRank[$i]
                              " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);   // 박스오피스 리스트를 구한다.

                    $QryBoxOffice = mysql_query($sQuery,$connect) ;
                    while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                    {
                        $Rank      = $ArrBoxOffice['Rank'];
                        $MovieCd   = $ArrBoxOffice['MovieCd'];
                        $MovieNm   = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
                        $MovieName = $Rank.". ".$MovieNm."(".$MovieCd.")";

                        // 영화리스트를 타이틀에 깐다..
                        ?>
                        <th colspan="5"><?=$MovieNm?></th>
                        <?
                    }
                }
                ?>
            </tr>
            <tr>
                <?
                // 영화리스트를 부 타이틀에 깐다..
                $arrRank = split(",",$Ranks);
                for ($i=0;$i< sizeof($arrRank);$i++)
                {
                    ?>
                    <th>상영관</th>
                    <th>좌석수</th>
                    <th>시간표</th>
                    <th>회차수</th>
                    <th>총좌석수</th>
                    <?
                }
                ?>
            </tr>
            <?
            $no = 1 ;
            $sQuery = "     SELECT *
                              FROM kofic_theather t
                        INNER JOIN (SELECT * FROM kofic_fix_theather WHERE Active = 1) f
                                ON t.Code = f.Code
                          ORDER BY f.`Group`
                                  ,f.Location
                                  ,t.TheatherName
                       " ;// echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);   // 극장리스트를 구한다.
            $QryKoficTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
            {
                $sTheatherCode = $ArrKoficTheather["Code"] ;
                $sTheatherName = $ArrKoficTheather["TheatherName"] ;
                //if  (($sTheatherCode != "001111")) continue; // CGV압구정

                $sTheatherName = iconv("EUC-KR", "UTF-8",$sTheatherName);
                ?>
                <tr>
                    <td class="ty1"><?=$no++?></td>
                    <td><?=$sTheatherName/*."(".$sTheatherCode.")"*/?></td>
                    <?
                        $arrRank = split(",",$Ranks);

                        $sQuery = "SELECT *
                                     FROM kofic_fix_boxoffice
                                    WHERE `Date` = '$BaseDate'
                                      AND Rank  IN (
                                  ";
                                for ($i=0;$i< sizeof($arrRank);$i++)
                                {
                                    $sQuery .= "$arrRank[$i]" ;
                                    if  ($i< sizeof($arrRank)-1)
                                        $sQuery .= ", " ;
                                }
                        $sQuery .= " )" ;
                        $sQuery .= " ORDER BY Rank" ;     //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);   // 박스오피스 리스트를 구한다.
                        $QryBoxOffice = mysql_query($sQuery,$connect) ;
                        while  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                        {
                                $Rank      = $ArrBoxOffice['Rank'];
                                $MovieCd   = $ArrBoxOffice['MovieCd'];
                                $MovieNm   = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);
                                $MovieName = $Rank.". ".$MovieNm;//."(".$MovieCd.")";

                                $ScrnNames = "";
                                $Seats     = "";
                                $ShowTmss  = "";
                                $cntInning = "";
                                $sumSeats  = "";


                                $sQuery = "    SELECT a.ScrnNm
                                                     ,b.Seat
                                                 FROM kofic_showtime a
                                            LEFT JOIN kofic_seat     b
                                                   ON b.TheatherCd = a.TheatherCd
                                                  AND b.ScrnNm     = a.ScrnNm
                                                WHERE 1 = 1
                                                  AND a.TheatherCd = '$sTheatherCode'
                                                  AND a.`Date`     = '$PlayDate'
                                                  AND a.MovieCd    = '$MovieCd'
                                             GROUP BY a.ScrnNm
                                          " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
                                $QryKoficPlaying = mysql_query($sQuery,$connect) ;
                                while  ($ArrKoficPlaying = mysql_fetch_array($QryKoficPlaying))
                                {
                                    $ScrnNm  = $ArrKoficPlaying['ScrnNm'];
                                    $ScrnNm_ = iconv("EUC-KR", "UTF-8",$ArrKoficPlaying['ScrnNm']);

                                    $Seat    = $ArrKoficPlaying['Seat'];

                                    $ShowTms = "";

                                    $cntSeat = 0;

                                    $sQuery = "    SELECT ShowTm
                                                     FROM kofic_showtime
                                                    WHERE 1 = 1
                                                      AND TheatherCd = '$sTheatherCode'
                                                      AND `Date`     = '$PlayDate'
                                                      AND ScrnNm     = '$ScrnNm'
                                                      AND MovieCd    = '$MovieCd'
                                                 ORDER BY Seq
                                              " ; //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);
                                    $QryShowTime = mysql_query($sQuery,$connect) ;
                                    while  ($ArrShowTime = mysql_fetch_array($QryShowTime))
                                    {
                                        $ShowTm = $ArrShowTime['ShowTm'] ;
                                        $ShowTm = substr($ShowTm,0,2) .":". substr($ShowTm,2,2);

                                        if  ($ShowTms != "") $ShowTms .= ",";

                                        $ShowTms .= $ShowTm;

                                        $cntSeat++;
                                    }
                                    $ShowTms .= " ";

                                    if  ($Seat==null)
                                    {
                                        //$ScrnNames .= "<a href=\"#\" onclick=\"setSeat('".$sTheatherCode."','".$ScrnNm_."')\">".$ScrnNm_."</a><br>";
										$ScrnNames .= $ScrnNm_."<br>";
                                        //$Seats     .= "없음<br>";
										$Seats     .= "<input class='edit_seat' type='text' value='' style='text-align:right;width: 40px;'size='3' theatherCd='$sTheatherCode' scrnNm='$ScrnNm_'><br>";


                                    }
                                    else
                                    {
                                        $ScrnNames .= $ScrnNm_."<br>";
                                        $Seats     .= $Seat."<br>";
                                    }

                                    $cntInning .= $cntSeat."<br>";
                                    if  ($ToExel=="Yes")  $sumSeats .= ($cntSeat*$Seat)."<br>";
                                    else                  $sumSeats .= number_format($cntSeat*$Seat)."<br>";
                                    $ShowTmss .= $ShowTms."<br>";
                                }
                                ?>
                                <td><?=$ScrnNames?></td>
                                <td class="ty2"><?=$Seats?></td>
                                <td style = "mso-number-format:\@"><?=$ShowTmss?></td>
                                <td class="ty2"><?=$cntInning?></td>
                                <td class="ty2"><?=$sumSeats?></td>
                                <?

                        }

                    ?>
                </tr>
                <?
                //echo "<tr>".$sTheatherCode."_".$sTheatherName." : 읽기";
            }
            ?>
            </table>
            </div>
            <?



            echo "<br>";
            //////                             //
            /////                             ///
            ////  경쟁영화 회차 비교표 2     ////
            ///                             /////
            //                             //////



            ?>
            <div class="t3">
                <table border="1">
                <tr>
                    <th rowspan="2" style="width: 200px;">영화명</th>
                    <th colspan="5">극장</th>
                    <th colspan="5">스크린</th>
                    <th colspan="5">회차</th>
                    <th colspan="5">총좌석수</th>
                </tr>
                <tr>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <!-- <th class="w100">프리</th> -->
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <!-- <th class="w100">프리</th> -->
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <!-- <th class="w100">프리</th> -->
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                    <th class="w100">CGV</th>
                    <th class="w100">롯데</th>
                    <th class="w100">메가</th>
                    <!-- <th class="w100">프리</th> -->
                    <th class="w100">기타</th>
                    <th class="w100">합계</th>
                </tr>
                <?
                $Script1 = "" ;
                $Script2 = "" ;
                $Script3 = "" ;
                $Script4 = "" ;

                $arrRank = split(",",$Ranks);
                for ($i=0;$i< sizeof($arrRank);$i++)
                {
                    $sQuery = "     SELECT *
                                     FROM kofic_fix_boxoffice
                                    WHERE `Date` = '$BaseDate'
                                      AND Rank = $arrRank[$i]
                              " ;  //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);   // 박스오피스 리스트를 구한다.
                    $QryBoxOffice = mysql_query($sQuery,$connect) ;
                    if  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                    {
                        $Rank     = $ArrBoxOffice['Rank'];
                        $MovieCd  = $ArrBoxOffice['MovieCd'];
                        $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);

    //if  ($MovieCd != "20148048") continue;
                        ?>

                            <?
                            $sQuery = "        SELECT '$MovieCd'                                    MovieCd
                                                      ,rpt.`Date`                                   `Date`
                                                      ,if(isnull(CntTheather1),0,CntTheather1)      CntTheather1
                                                      ,if(isnull(CntTheather2),0,CntTheather2)      CntTheather2
                                                      ,if(isnull(CntTheather3),0,CntTheather3)      CntTheather3
                                                      ,if(isnull(CntTheather4),0,CntTheather4)      CntTheather4
                                                      ,if(isnull(CntTheather9),0,CntTheather9)      CntTheather9
                                                      ,if(isnull(CntTheatherSum),0,CntTheatherSum)  CntTheatherSum
                                                      ,if(isnull(CntScrn1),0,CntScrn1)              CntScrn1
                                                      ,if(isnull(CntScrn2),0,CntScrn2)              CntScrn2
                                                      ,if(isnull(CntScrn3),0,CntScrn3)              CntScrn3
                                                      ,if(isnull(CntScrn4),0,CntScrn4)              CntScrn4
                                                      ,if(isnull(CntScrn9),0,CntScrn9)              CntScrn9
                                                      ,if(isnull(CntScrnSum),0,CntScrnSum)          CntScrnSum
                                                      ,if(isnull(CntShowTm1),0,CntShowTm1)          CntShowTm1
                                                      ,if(isnull(CntShowTm2),0,CntShowTm2)          CntShowTm2
                                                      ,if(isnull(CntShowTm3),0,CntShowTm3)          CntShowTm3
                                                      ,if(isnull(CntShowTm4),0,CntShowTm4)          CntShowTm4
                                                      ,if(isnull(CntShowTm9),0,CntShowTm9)          CntShowTm9
                                                      ,if(isnull(CntShowTmSum),0,CntShowTmSum)      CntShowTmSum
                                                      ,if(isnull(SumSeat1),0,SumSeat1)              SumSeat1
                                                      ,if(isnull(SumSeat2),0,SumSeat2)              SumSeat2
                                                      ,if(isnull(SumSeat3),0,SumSeat3)              SumSeat3
                                                      ,if(isnull(SumSeat4),0,SumSeat4)              SumSeat4
                                                      ,if(isnull(SumSeat9),0,SumSeat9)              SumSeat9
                                                      ,if(isnull(SumSeatSum),0,SumSeatSum)          SumSeatSum
                                                 FROM ( SELECT A.MovieCd
                                                              ,A.`Date`
                                                              ,SUM(if (C.X = 0 and A.grp=1, A.CntTheather,0)) CntTheather1
                                                              ,SUM(if (C.X = 0 and A.grp=2, A.CntTheather,0)) CntTheather2
                                                              ,SUM(if (C.X = 0 and A.grp=3, A.CntTheather,0)) CntTheather3
                                                              ,SUM(if (C.X = 0 and A.grp=4, A.CntTheather,0)) CntTheather4
                                                              ,SUM(if (C.X = 0 and A.grp=9, A.CntTheather,0)) CntTheather9
                                                              ,SUM(if (C.X = 0 and A.grp=1, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=2, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=3, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=4, A.CntTheather,0))
                                                              +SUM(if (C.X = 0 and A.grp=9, A.CntTheather,0)) CntTheatherSum
                                                              ,SUM(if (C.X = 1 and A.grp=1, A.CntScrn,0)) CntScrn1
                                                              ,SUM(if (C.X = 1 and A.grp=2, A.CntScrn,0)) CntScrn2
                                                              ,SUM(if (C.X = 1 and A.grp=3, A.CntScrn,0)) CntScrn3
                                                              ,SUM(if (C.X = 1 and A.grp=4, A.CntScrn,0)) CntScrn4
                                                              ,SUM(if (C.X = 1 and A.grp=9, A.CntScrn,0)) CntScrn9
                                                              ,SUM(if (C.X = 1 and A.grp=1, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=2, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=3, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=4, A.CntScrn,0))
                                                              +SUM(if (C.X = 1 and A.grp=9, A.CntScrn,0)) CntScrnSum
                                                              ,SUM(if (C.X = 2 and A.grp=1, A.CntShowTm,0)) CntShowTm1
                                                              ,SUM(if (C.X = 2 and A.grp=2, A.CntShowTm,0)) CntShowTm2
                                                              ,SUM(if (C.X = 2 and A.grp=3, A.CntShowTm,0)) CntShowTm3
                                                              ,SUM(if (C.X = 2 and A.grp=4, A.CntShowTm,0)) CntShowTm4
                                                              ,SUM(if (C.X = 2 and A.grp=9, A.CntShowTm,0)) CntShowTm9
                                                              ,SUM(if (C.X = 2 and A.grp=1, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=2, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=3, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=4, A.CntShowTm,0))
                                                              +SUM(if (C.X = 2 and A.grp=9, A.CntShowTm,0)) CntShowTmSum
                                                              ,SUM(if (C.X = 3 and A.grp=1, A.SumSeat,0)) SumSeat1
                                                              ,SUM(if (C.X = 3 and A.grp=2, A.SumSeat,0)) SumSeat2
                                                              ,SUM(if (C.X = 3 and A.grp=3, A.SumSeat,0)) SumSeat3
                                                              ,SUM(if (C.X = 3 and A.grp=4, A.SumSeat,0)) SumSeat4
                                                              ,SUM(if (C.X = 3 and A.grp=9, A.SumSeat,0)) SumSeat9
                                                              ,SUM(if (C.X = 3 and A.grp=1, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=2, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=3, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=4, A.SumSeat,0))
                                                              +SUM(if (C.X = 3 and A.grp=9, A.SumSeat,0)) SumSeatSum
                                                          FROM (
                                                                  SELECT a.MovieCd
                                                                        ,a.`Date`
                                                                        ,a.grp
                                                                        ,CntTheather
                                                                        ,CntScrn
                                                                        ,CntShowTm
                                                                        ,SumSeat
                                                                    FROM (
                                                                                SELECT tmp.grp
                                                                                      ,ply.`Date`
                                                                                      ,ply.MovieCd
                                                                                      ,IFNULL(ply.CntTheather, 0) CntTheather
                                                                                  FROM ( SELECT 1 grp
                                                                                          UNION ALL SELECT 2
                                                                                          UNION ALL SELECT 3
                                                                                          UNION ALL SELECT 4
                                                                                          UNION ALL SELECT 9
                                                                                        ) tmp
                                                                              LEFT JOIN (   SELECT IF(fix.`Group`='',9,fix.`Group`) `Group`
                                                                                                  ,play.`Date`
                                                                                                  ,play.MovieCd
                                                                                                  ,count( play.TheatherCd ) CntTheather
                                                                                              FROM (
                                                                                                      SELECT stm.TheatherCd, stm.`Date`, stm.MovieCd
                                                                                                        FROM kofic_showtime stm
                                                                                                  INNER JOIN kofic_seat st
                                                                                                          ON st.TheatherCd = stm.TheatherCd
                                                                                                         AND st.ScrnNm     = stm.ScrnNm
                                                                                                       WHERE `Date`  = '$PlayDate'
                                                                                                         AND MovieCd = '$MovieCd'
                                                                                                    GROUP BY TheatherCd, `Date` , MovieCd
                                                                                                  ) play
                                                                                       INNER JOIN (select * from kofic_fix_theather where Active = 1) fix
                                                                                               ON fix.Code = play.TheatherCd
                                                                                            WHERE 1 =1
                                                                                              AND play.MovieCd = '$MovieCd'
                                                                                         GROUP BY fix.`Group`
                                                                                                 ,play.`Date`
                                                                                        ) ply
                                                                                    ON ply.`Group` = tmp.grp
                                                                              ORDER BY tmp.grp
                                                                        ) c,
                                                                        (
                                                                                SELECT tmp.grp
                                                                                      ,ply.`Date`
                                                                                      ,ply.MovieCd
                                                                                      ,IFNULL(ply.CntScrn, 0) CntScrn
                                                                                  FROM ( SELECT 1 grp
                                                                                          UNION ALL SELECT 2
                                                                                          UNION ALL SELECT 3
                                                                                          UNION ALL SELECT 4
                                                                                          UNION ALL SELECT 9
                                                                                        ) tmp
                                                                              LEFT JOIN (   SELECT IF(fix.`Group`='',9,fix.`Group`) `Group`
                                                                                                  ,play.`Date`
                                                                                                  ,play.MovieCd
                                                                                                  ,count( play.ScrnNm ) CntScrn
                                                                                              FROM (
                                                                                                      SELECT stm.TheatherCd, stm.`Date`, stm.ScrnNm, stm.MovieCd
                                                                                                        FROM kofic_showtime stm
                                                                                                  INNER JOIN kofic_seat st
                                                                                                          ON st.TheatherCd = stm.TheatherCd
                                                                                                         AND st.ScrnNm     = stm.ScrnNm
                                                                                                       WHERE `Date`  = '$PlayDate'
                                                                                                         AND MovieCd = '$MovieCd'
                                                                                                    GROUP BY stm.TheatherCd, stm.`Date`, stm.ScrnNm, stm.MovieCd
                                                                                                  ) play
                                                                                       INNER JOIN (select * from kofic_fix_theather where Active = 1) fix
                                                                                               ON fix.Code = play.TheatherCd
                                                                                            WHERE 1 =1
                                                                                              AND play.MovieCd = '$MovieCd'
                                                                                         GROUP BY fix.`Group`
                                                                                                 ,play.`Date`
                                                                                        ) ply
                                                                                    ON ply.`Group` = tmp.grp
                                                                              ORDER BY tmp.grp
                                                                        ) a,
                                                                        (
                                                                              SELECT tmp.grp
                                                                                    ,`Date`
                                                                                    ,ply.MovieCd
                                                                                    ,IFNULL(ply.CntShowTm, 0) CntShowTm
                                                                                    ,IFNULL(ply.SumSeat, 0) SumSeat
                                                                                FROM ( SELECT 1 grp
                                                                                      UNION ALL SELECT 2
                                                                                      UNION ALL SELECT 3
                                                                                      UNION ALL SELECT 4
                                                                                      UNION ALL SELECT 9
                                                                                    ) tmp
                                                                          LEFT JOIN (   SELECT IF( fix.`Group` = '', 9, fix.`Group` ) `Group`
                                                                                              ,play.`Date`
                                                                                              ,play.MovieCd
                                                                                              ,count( play.ShowTm ) CntShowTm
                                                                                              ,sum(seat.Seat)       SumSeat
                                                                                          FROM kofic_showtime  play
                                                                                    INNER JOIN (select * from kofic_fix_theather where Active = 1) fix
                                                                                            ON fix.Code = play.TheatherCd
                                                                                    INNER JOIN kofic_seat seat
                                                                                            ON seat.TheatherCd  = play.TheatherCd
                                                                                           AND seat.ScrnNm      = play.ScrnNm
                                                                                         WHERE 1 =1
                                                                                           AND play.`Date`  = '$PlayDate'
                                                                                           AND play.MovieCd = '$MovieCd'
                                                                                      GROUP BY fix.`Group`
                                                                                              ,play.`Date`
                                                                                    ) ply
                                                                                  ON ply.`Group` = tmp.grp
                                                                            ORDER BY tmp.grp
                                                                        ) b
                                                                  WHERE a.grp    = b.grp
                                                                    AND a.`Date` = b.`Date`
                                                                    And c.grp    = b.grp
                                                                    AND c.`Date` = b.`Date`
                                                               ) A
                                                              ,(
                                                                  SELECT 0 X
                                                                  UNION ALL
                                                                  SELECT 1
                                                                  UNION ALL
                                                                  SELECT 2
                                                                  UNION ALL
                                                                  SELECT 3
                                                               )  C
                                                         WHERE 1=1
                                                      GROUP BY A.MovieCd
                                                             ,A.`Date`
                                                      ) rpt
                                      " ;  // echo "<br><br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
                            $QryReport = mysql_query($sQuery,$connect) ; $chkOne = 0;
                            while ($ArrReport = mysql_fetch_array($QryReport))
                            {
                              $chkOne ++ ;
                              if  ($ArrReport["Date"] == $PlayDate)
                              {
                                ?>
                                <tr>
                                <td rowspan=2><?=$MovieNm?></td>
                                <?
                                $CntTheather1  = $ArrReport["CntTheather1"] ;
                                $CntTheather2  = $ArrReport["CntTheather2"] ;
                                $CntTheather3  = $ArrReport["CntTheather3"] ;
                                $CntTheather4  = $ArrReport["CntTheather4"] ;
                                $CntTheather9  = $ArrReport["CntTheather9"] ;
                                $CntTheatherSum= $ArrReport["CntTheatherSum"] ;
                                $CntScrn1      = $ArrReport["CntScrn1"] ;
                                $CntScrn2      = $ArrReport["CntScrn2"] ;
                                $CntScrn3      = $ArrReport["CntScrn3"] ;
                                $CntScrn4      = $ArrReport["CntScrn4"] ;
                                $CntScrn9      = $ArrReport["CntScrn9"] ;
                                $CntScrnSum    = $ArrReport["CntScrnSum"] ;
                                $CntShowTm1    = $ArrReport["CntShowTm1"] ;
                                $CntShowTm2    = $ArrReport["CntShowTm2"] ;
                                $CntShowTm3    = $ArrReport["CntShowTm3"] ;
                                $CntShowTm4    = $ArrReport["CntShowTm4"] ;
                                $CntShowTm9    = $ArrReport["CntShowTm9"] ;
                                $CntShowTmSum  = $ArrReport["CntShowTmSum"] ;
                                $SumSeat1      = $ArrReport["SumSeat1"] ;
                                $SumSeat2      = $ArrReport["SumSeat2"] ;
                                $SumSeat3      = $ArrReport["SumSeat3"] ;
                                $SumSeat4      = $ArrReport["SumSeat4"] ;
                                $SumSeat9      = $ArrReport["SumSeat9"] ;
                                $SumSeatSum    = $ArrReport["SumSeatSum"] ;

                                $Script1 .= "chartData1.push({ \"category\": \"$MovieNm\",  \"value\": $CntTheatherSum }); \n" ;
                                $Script2 .= "chartData2.push({ \"category\": \"$MovieNm\",  \"value\": $CntScrnSum }); \n" ;
                                $Script3 .= "chartData3.push({ \"category\": \"$MovieNm\",  \"value\": $CntShowTmSum }); \n" ;
                                $Script4 .= "chartData4.push({ \"category\": \"$MovieNm\",  \"value\": $SumSeatSum }); \n" ;

                                ?><td class="ty2"><?=number_format($CntTheather1)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheather2)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheather3)?></td><?
                                ?><!--<td class="ty2"><?=number_format($CntTheather4)?></td>--><?
                                ?><td class="ty2"><?=number_format($CntTheather9)?></td><?
                                ?><td class="ty2"><?=number_format($CntTheatherSum)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn1)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn2)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrn3)?></td><?
                                ?><!--<td class="ty2"><?=number_format($CntScrn4)?></td>--><?
                                ?><td class="ty2"><?=number_format($CntScrn9)?></td><?
                                ?><td class="ty2"><?=number_format($CntScrnSum)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm1)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm2)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTm3)?></td><?
                                ?><!--<td class="ty2"><?=number_format($CntShowTm4)?></td>--><?
                                ?><td class="ty2"><?=number_format($CntShowTm9)?></td><?
                                ?><td class="ty2"><?=number_format($CntShowTmSum)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat1)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat2)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeat3)?></td><?
                                ?><!--<td class="ty2"><?=number_format($SumSeat4)?></td>--><?
                                ?><td class="ty2"><?=number_format($SumSeat9)?></td><?
                                ?><td class="ty2"><?=number_format($SumSeatSum)?></td><?
                                ?>
                                </tr>
                                <?
                              }


                              if  ($chkOne==1)
                              {
                                ?>
                                <tr>
                                <?

                                $RtCntTheather1  = (($CntTheather1>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather1 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather2  = (($CntTheather2>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather2 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather3  = (($CntTheather3>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather3 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather4  = (($CntTheather4>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather4 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheather9  = (($CntTheather9>0) && ($CntTheatherSum>0))  ? number_format(($CntTheather9 / $CntTheatherSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntTheatherSum= ($CntTheatherSum>0) ? "100 %" : "0.0 %" ;
                                $RtCntScrn1      = (($CntScrn1>0) && ($CntScrnSum>0))  ? number_format(($CntScrn1 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn2      = (($CntScrn2>0) && ($CntScrnSum>0))  ? number_format(($CntScrn2 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn3      = (($CntScrn3>0) && ($CntScrnSum>0))  ? number_format(($CntScrn3 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn4      = (($CntScrn4>0) && ($CntScrnSum>0))  ? number_format(($CntScrn4 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrn9      = (($CntScrn9>0) && ($CntScrnSum>0))  ? number_format(($CntScrn9 / $CntScrnSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntScrnSum    = ($CntScrnSum>0) ? "100 %" : "0.0 %" ;
                                $RtCntShowTm1    = (($CntShowTm1>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm1 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm2    = (($CntShowTm2>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm2 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm3    = (($CntShowTm3>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm3 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm4    = (($CntShowTm4>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm4 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTm9    = (($CntShowTm9>0) && ($CntShowTmSum>0))  ? number_format(($CntShowTm9 / $CntShowTmSum)*100.0,1)." %" : "0.0 %" ;
                                $RtCntShowTmSum  = ($CntShowTmSum>0) ? "100 %" : "0.0 %" ;
                                $RtSumSeat1      = (($SumSeat1>0) && ($SumSeatSum>0))  ? number_format(($SumSeat1 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat2      = (($SumSeat2>0) && ($SumSeatSum>0))  ? number_format(($SumSeat2 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat3      = (($SumSeat3>0) && ($SumSeatSum>0))  ? number_format(($SumSeat3 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat4      = (($SumSeat4>0) && ($SumSeatSum>0))  ? number_format(($SumSeat4 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeat9      = (($SumSeat9>0) && ($SumSeatSum>0))  ? number_format(($SumSeat9 / $SumSeatSum)*100.0,1)." %" : "0.0 %" ;
                                $RtSumSeatSum    = ($SumSeatSum>0) ? "100 %" : "0.0 %" ;


                                ?><td class="ty2"><?=$RtCntTheather1?></td><?
                                ?><td class="ty2"><?=$RtCntTheather2?></td><?
                                ?><td class="ty2"><?=$RtCntTheather3?></td><?
                                ?><!--<td class="ty2"><?=$RtCntTheather4?></td>--><?
                                ?><td class="ty2"><?=$RtCntTheather9?></td><?
                                ?><td class="ty2"><?=$RtCntTheatherSum?></td><?
                                ?><td class="ty2"><?=$RtCntScrn1?></td><?
                                ?><td class="ty2"><?=$RtCntScrn2?></td><?
                                ?><td class="ty2"><?=$RtCntScrn3?></td><?
                                ?><!--<td class="ty2"><?=$RtCntScrn4?></td>--><?
                                ?><td class="ty2"><?=$RtCntScrn9?></td><?
                                ?><td class="ty2"><?=$RtCntScrnSum?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm1?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm2?></td><?
                                ?><td class="ty2"><?=$RtCntShowTm3?></td><?
                                ?><!--<td class="ty2"><?=$RtCntShowTm4?></td>--><?
                                ?><td class="ty2"><?=$RtCntShowTm9?></td><?
                                ?><td class="ty2"><?=$RtCntShowTmSum?></td><?
                                ?><td class="ty2"><?=$RtSumSeat1?></td><?
                                ?><td class="ty2"><?=$RtSumSeat2?></td><?
                                ?><td class="ty2"><?=$RtSumSeat3?></td><?
                                ?><!--<td class="ty2"><?=$RtSumSeat4?></td>--><?
                                ?><td class="ty2"><?=$RtSumSeat9?></td><?
                                ?><td class="ty2"><?=$RtSumSeatSum?></td><?
                                ?>
                                </tr>
                                <?
                              }

                              /*
                              if  ($ArrReport["Date"] == $WeekAgoDay)
                              {
                                $GapCntScrn1      = $CntScrn1 - $ArrReport["CntScrn1"] ;
                                $GapCntScrn2      = $CntScrn2 - $ArrReport["CntScrn2"] ;
                                $GapCntScrn3      = $CntScrn3 - $ArrReport["CntScrn3"] ;
                                $GapCntScrn4      = $CntScrn4 - $ArrReport["CntScrn4"] ;
                                $GapCntScrn9      = $CntScrn9 - $ArrReport["CntScrn9"] ;
                                $GapCntScrnSum    = $CntScrnSum - $ArrReport["CntScrnSum"] ;
                                $GapCntShowTm1    = $CntShowTm1 - $ArrReport["CntShowTm1"] ;
                                $GapCntShowTm2    = $CntShowTm2 - $ArrReport["CntShowTm2"] ;
                                $GapCntShowTm3    = $CntShowTm3 - $ArrReport["CntShowTm3"] ;
                                $GapCntShowTm4    = $CntShowTm4 - $ArrReport["CntShowTm4"] ;
                                $GapCntShowTm9    = $CntShowTm9 - $ArrReport["CntShowTm9"] ;
                                $GapCntShowTmSum  = $CntShowTmSum - $ArrReport["CntShowTmSum"] ;
                                $GapSumSeat1      = $SumSeat1 - $ArrReport["SumSeat1"] ;
                                $GapSumSeat2      = $SumSeat2 - $ArrReport["SumSeat2"] ;
                                $GapSumSeat3      = $SumSeat3 - $ArrReport["SumSeat3"] ;
                                $GapSumSeat4      = $SumSeat4 - $ArrReport["SumSeat4"] ;
                                $GapSumSeat9      = $SumSeat9 - $ArrReport["SumSeat9"] ;
                                $GapSumSeatSum    = $SumSeatSum - $ArrReport["SumSeatSum"] ;

                                $SgnCntScrn1     = GetSign($GapCntScrn1) ;
                                $SgnCntScrn2     = GetSign($GapCntScrn2) ;
                                $SgnCntScrn3     = GetSign($GapCntScrn3) ;
                                $SgnCntScrn4     = GetSign($GapCntScrn4) ;
                                $SgnCntScrn9     = GetSign($GapCntScrn9) ;
                                $SgnCntScrnSum   = GetSign($GapCntScrnSum) ;
                                $SgnCntShowTm1   = GetSign($GapCntShowTm1) ;
                                $SgnCntShowTm2   = GetSign($GapCntShowTm2) ;
                                $SgnCntShowTm3   = GetSign($GapCntShowTm3) ;
                                $SgnCntShowTm4   = GetSign($GapCntShowTm4) ;
                                $SgnCntShowTm9   = GetSign($GapCntShowTm9) ;
                                $SgnCntShowTmSum = GetSign($GapCntShowTmSum) ;
                                $SgnSumSeat1     = GetSign($GapSumSeat1) ;
                                $SgnSumSeat2     = GetSign($GapSumSeat2) ;
                                $SgnSumSeat3     = GetSign($GapSumSeat3) ;
                                $SgnSumSeat4     = GetSign($GapSumSeat4) ;
                                $SgnSumSeat9     = GetSign($GapSumSeat9) ;
                                $SgnSumSeatSum   = GetSign($GapSumSeatSum) ;


                                ?>
                                <tr>
                                <td class="ty2">전일대비</td>
                                <?
                                ?><td class="ty2"><?=number_format($GapCntScrn1)." ".$SgnCntScrn1?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn2)." ".$SgnCntScrn2?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn3)." ".$SgnCntScrn3?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn4)." ".$SgnCntScrn4?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrn9)." ".$SgnCntScrn9?></td><?
                                ?><td class="ty2"><?=number_format($GapCntScrnSum)." ".$SgnCntScrnSum?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm1)." ".$SgnCntShowTm1?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm2)." ".$SgnCntShowTm2?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm3)." ".$SgnCntShowTm3?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm4)." ".$SgnCntShowTm4?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTm9)." ".$SgnCntShowTm9?></td><?
                                ?><td class="ty2"><?=number_format($GapCntShowTmSum)." ".$SgnCntShowTmSum?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat1)." ".$SgnSumSeat1?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat2)." ".$SgnSumSeat2?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat3)." ".$SgnSumSeat3?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat4)." ".$SgnSumSeat4?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeat9)." ".$SgnSumSeat9?></td><?
                                ?><td class="ty2"><?=number_format($GapSumSeatSum)." ".$SgnSumSeatSum?></td><?
                                ?>
                                </tr>
                                <?
                              }
                              */
                            }


                            //echo "<br>".$MovieNm."(".$MovieCd.")";
                    }
                }
                ?>
                </table>
            </div>
            <?


            echo "<br>";
            //////                             //
            /////                             ///
            ////  경쟁영화 회차 비교표 3     ////
            ///                             /////
            //                             //////


            ?>

                <div class="t3">
                    <table border="1">
                    <tr>
                        <th rowspan="2" style="width: 200px;">영화명</th>
                        <th colspan="4">CGV</th>
                        <th colspan="4">롯데시네마</th>
                        <th colspan="4">메가박스</th>
                        <th colspan="4"><!--프리머스,-->기타</th>
                        <th colspan="4">총계</th>
                    </tr>
                    <tr>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                        <th class="w100">스크린</th>
                        <th class="w100">좌석수</th>
                        <th class="w100">총회차</th>
                        <th class="w100">총좌석수</th>
                    </tr>
                    <?
                    $chgMovie    = "";
                    $optionMovie = "";

                    $Sum_A_CntScrnNm     = 0 ;
                    $Sum_A_SumScrnSeat   = 0 ;
                    $Sum_A_CntShowTm     = 0 ;
                    $Sum_A_SumShowSeat   = 0 ;
                    $Sum_B_CntScrnNm     = 0 ;
                    $Sum_B_SumScrnSeat   = 0 ;
                    $Sum_B_CntShowTm     = 0 ;
                    $Sum_B_SumShowSeat   = 0 ;
                    $Sum_C_CntScrnNm     = 0 ;
                    $Sum_C_SumScrnSeat   = 0 ;
                    $Sum_C_CntShowTm     = 0 ;
                    $Sum_C_SumShowSeat   = 0 ;
                    $Sum_D_CntScrnNm     = 0 ;
                    $Sum_D_SumScrnSeat   = 0 ;
                    $Sum_D_CntShowTm     = 0 ;
                    $Sum_D_SumShowSeat   = 0 ;
                    $Sum_All_CntScrnNm   = 0 ;
                    $Sum_All_SumScrnSeat = 0 ;
                    $Sum_All_CntShowTm   = 0 ;
                    $Sum_All_SumShowSeat = 0 ;

                    $arrRank = split(",",$Ranks);
                    for ($i=0;$i< sizeof($arrRank);$i++)
                    {
                        $sQuery = "     SELECT *
                                         FROM kofic_fix_boxoffice
                                        WHERE `Date` = '$BaseDate'
                                          AND Rank = $arrRank[$i]
                                  " ; // echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery);   // 박스오피스 리스트를 구한다.
                        $QryBoxOffice = mysql_query($sQuery,$connect) ;
                        if  ($ArrBoxOffice = mysql_fetch_array($QryBoxOffice))
                        {
                            $Rank     = $ArrBoxOffice['Rank'];
                            $MovieCd  = $ArrBoxOffice['MovieCd'];
                            $MovieNm  = iconv("EUC-KR", "UTF-8",$ArrBoxOffice['MovieNm']);

                            //if  ($MovieCd != "20148048") continue;
                            //$MovieCd = '20161364';
                            //echo "<br><br>".$PlayDate ;
                            //echo "<br><br>".$MovieCd ;

                            $sQuery = "         SELECT IFNULL(SUM(A_CntScrnNm),0)      A_CntScrnNm
                                                      ,IFNULL(SUM(A_SumScrnSeat),0)    A_SumScrnSeat
                                                      ,IFNULL(SUM(A_CntShowTm),0)      A_CntShowTm
                                                      ,IFNULL(SUM(A_SumShowSeat),0)    A_SumShowSeat
                                                      ,IFNULL(SUM(B_CntScrnNm),0)      B_CntScrnNm
                                                      ,IFNULL(SUM(B_SumScrnSeat),0)    B_SumScrnSeat
                                                      ,IFNULL(SUM(B_CntShowTm),0)      B_CntShowTm
                                                      ,IFNULL(SUM(B_SumShowSeat),0)    B_SumShowSeat
                                                      ,IFNULL(SUM(C_CntScrnNm),0)      C_CntScrnNm
                                                      ,IFNULL(SUM(C_SumScrnSeat),0)    C_SumScrnSeat
                                                      ,IFNULL(SUM(C_CntShowTm),0)      C_CntShowTm
                                                      ,IFNULL(SUM(C_SumShowSeat),0)    C_SumShowSeat
                                                      ,IFNULL(SUM(D_CntScrnNm),0)      D_CntScrnNm
                                                      ,IFNULL(SUM(D_SumScrnSeat),0)    D_SumScrnSeat
                                                      ,IFNULL(SUM(D_CntShowTm),0)      D_CntShowTm
                                                      ,IFNULL(SUM(D_SumShowSeat),0)    D_SumShowSeat
                                                      ,IFNULL(SUM(All_CntScrnNm),0)    All_CntScrnNm
                                                      ,IFNULL(SUM(All_SumScrnSeat),0)  All_SumScrnSeat
                                                      ,IFNULL(SUM(All_CntShowTm),0)    All_CntShowTm
                                                      ,IFNULL(SUM(All_SumShowSeat),0)  All_SumShowSeat
                                                FROM
                                                (
                                                    SELECT IF(GCODE='A',CntScrnNm,NULL)       A_CntScrnNm
                                                          ,IF(GCODE='A',SumScrnSeat,NULL)     A_SumScrnSeat
                                                          ,IF(GCODE='A',CntShowTm,NULL)       A_CntShowTm
                                                          ,IF(GCODE='A',SumShowSeat,NULL)     A_SumShowSeat
                                                          ,IF(GCODE='B',CntScrnNm,NULL)       B_CntScrnNm
                                                          ,IF(GCODE='B',SumScrnSeat,NULL)     B_SumScrnSeat
                                                          ,IF(GCODE='B',CntShowTm,NULL)       B_CntShowTm
                                                          ,IF(GCODE='B',SumShowSeat,NULL)     B_SumShowSeat
                                                          ,IF(GCODE='C',CntScrnNm,NULL)       C_CntScrnNm
                                                          ,IF(GCODE='C',SumScrnSeat,NULL)     C_SumScrnSeat
                                                          ,IF(GCODE='C',CntShowTm,NULL)       C_CntShowTm
                                                          ,IF(GCODE='C',SumShowSeat,NULL)     C_SumShowSeat
                                                          ,IF(GCODE='D',CntScrnNm,NULL)       D_CntScrnNm
                                                          ,IF(GCODE='D',SumScrnSeat,NULL)     D_SumScrnSeat
                                                          ,IF(GCODE='D',CntShowTm,NULL)       D_CntShowTm
                                                          ,IF(GCODE='D',SumShowSeat,NULL)     D_SumShowSeat
                                                          ,IF(ISNULL(GCODE),CntScrnNm,NULL)   All_CntScrnNm
                                                          ,IF(ISNULL(GCODE),SumScrnSeat,NULL) All_SumScrnSeat
                                                          ,IF(ISNULL(GCODE),CntShowTm,NULL)   All_CntShowTm
                                                          ,IF(ISNULL(GCODE),SumShowSeat,NULL) All_SumShowSeat
                                                    FROM
                                                    (
                                                          SELECT GCODE
                                                                ,SUM(SHOWROOM.CntScrnNm) CntScrnNm
                                                                ,SUM(SHOWROOM.SumSeat) SumScrnSeat
                                                                ,SUM(INNING.CntShowTm) CntShowTm
                                                                ,SUM(INNING.SumSeat)  SumShowSeat
                                                            FROM (
                                                                      SELECT GRP.GCODE, FT.Code
                                                                        FROM kofic_fix_theather FT
                                                                  INNER JOIN (        SELECT 'A' GCODE,1 CND1,1 CND2
                                                                            UNION ALL SELECT 'B'      ,2     ,2
                                                                            UNION ALL SELECT 'C'      ,3     ,3
                                                                            UNION ALL SELECT 'D'      ,4     ,''
                                                                            ) GRP
                                                                         ON FT.`Group` = GRP.CND1
                                                                         OR FT.`Group` = GRP.CND2
                                                                      WHERE FT.Active = 1
                                                                 ) THEATHER
                                                      INNER JOIN (
                                                                    SELECT `Date`, MovieCd, TheatherCd, COUNT(ScrnNm) CntScrnNm, SUM(Seat) SumSeat
                                                                      FROM (
                                                                              SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, st.Seat
                                                                                FROM kofic_showtime stm
                                                                          INNER JOIN kofic_seat st
                                                                                  ON st.TheatherCd = stm.TheatherCd
                                                                                 AND st.ScrnNm     = stm.ScrnNm
                                                                               WHERE stm.`Date`  = '$PlayDate'
                                                                                 AND stm.MovieCd = '$MovieCd'
                                                                            GROUP BY stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm
                                                                           ) A
                                                                  GROUP BY `Date`, MovieCd, TheatherCd
                                                                 ) SHOWROOM
                                                              ON SHOWROOM.TheatherCd =  THEATHER.Code
                                                      INNER JOIN (
                                                                    SELECT `Date`, MovieCd, TheatherCd, COUNT(ShowTm) CntShowTm, SUM(Seat) SumSeat
                                                                      FROM (
                                                                                SELECT stm.`Date`, stm.MovieCd, stm.TheatherCd, stm.ScrnNm, stm.ShowTm, st.Seat
                                                                                  FROM kofic_showtime stm
                                                                            INNER JOIN kofic_seat st
                                                                                    ON st.TheatherCd = stm.TheatherCd
                                                                                   AND st.ScrnNm     = stm.ScrnNm
                                                                                 WHERE stm.`Date`  = '$PlayDate'
                                                                                   AND stm.MovieCd = '$MovieCd'
                                                                           ) A
                                                                  GROUP BY `Date`, MovieCd, TheatherCd
                                                                 ) INNING
                                                              ON INNING.TheatherCd = THEATHER.Code
                                                        GROUP BY THEATHER.GCODE WITH ROLLUP
                                                    ) A
                                                ) A
                                      " ;   // echo "<br><br>".iconv("EUC-KR", "UTF-8",$sQuery); ;   // 박스오피스 리스트를 구한다.
                            $QryReport = mysql_query($sQuery,$connect) ;
                            while ($ArrReport = mysql_fetch_array($QryReport))
                            {
                                ?>
                                <tr>
                                <td><?=$MovieNm?></td>
                                <?
                                $A_CntScrnNm      = $ArrReport["A_CntScrnNm"] ;
                                $A_SumScrnSeat    = $ArrReport["A_SumScrnSeat"] ;
                                $A_CntShowTm      = $ArrReport["A_CntShowTm"] ;
                                $A_SumShowSeat    = $ArrReport["A_SumShowSeat"] ;
                                $B_CntScrnNm      = $ArrReport["B_CntScrnNm"] ;
                                $B_SumScrnSeat    = $ArrReport["B_SumScrnSeat"] ;
                                $B_CntShowTm      = $ArrReport["B_CntShowTm"] ;
                                $B_SumShowSeat    = $ArrReport["B_SumShowSeat"] ;
                                $C_CntScrnNm      = $ArrReport["C_CntScrnNm"] ;
                                $C_SumScrnSeat    = $ArrReport["C_SumScrnSeat"] ;
                                $C_CntShowTm      = $ArrReport["C_CntShowTm"] ;
                                $C_SumShowSeat    = $ArrReport["C_SumShowSeat"] ;
                                $D_CntScrnNm      = $ArrReport["D_CntScrnNm"] ;
                                $D_SumScrnSeat    = $ArrReport["D_SumScrnSeat"] ;
                                $D_CntShowTm      = $ArrReport["D_CntShowTm"] ;
                                $D_SumShowSeat    = $ArrReport["D_SumShowSeat"] ;
                                $All_CntScrnNm    = $ArrReport["All_CntScrnNm"] ;
                                $All_SumScrnSeat  = $ArrReport["All_SumScrnSeat"] ;
                                $All_CntShowTm    = $ArrReport["All_CntShowTm"] ;
                                $All_SumShowSeat  = $ArrReport["All_SumShowSeat"] ;

                                $optionMovie .= "\n<option value=\"$Rank\">&nbsp;".$MovieNm ;
                                $chgMovie    .= "\n if  (val==$Rank)
                                                    {
                                                        chartData1 = [];
                                                        chartData1.push({ \"category\": \"CGV\",           \"value\": $A_CntScrnNm });
                                                        chartData1.push({ \"category\": \"롯데시네마\",    \"value\": $B_CntScrnNm });
                                                        chartData1.push({ \"category\": \"메가박스\",      \"value\": $C_CntScrnNm });
                                                        chartData1.push({ \"category\": \"기타\", \"value\": $D_CntScrnNm });

                                                        chart1.dataProvider = chartData1;
                                                        chart1.validateData();

                                                        chartData2 = [];
                                                        chartData2.push({ \"category\": \"CGV\",           \"value\": $A_SumScrnSeat });
                                                        chartData2.push({ \"category\": \"롯데시네마\",    \"value\": $B_SumScrnSeat });
                                                        chartData2.push({ \"category\": \"메가박스\",      \"value\": $C_SumScrnSeat });
                                                        chartData2.push({ \"category\": \"기타\", \"value\": $D_SumScrnSeat });

                                                        chart2.dataProvider = chartData2;
                                                        chart2.validateData();

                                                        chartData3 = [];
                                                        chartData3.push({ \"category\": \"CGV\",           \"value\": $A_CntShowTm });
                                                        chartData3.push({ \"category\": \"롯데시네마\",    \"value\": $B_CntShowTm });
                                                        chartData3.push({ \"category\": \"메가박스\",      \"value\": $C_CntShowTm });
                                                        chartData3.push({ \"category\": \"기타\", \"value\": $D_CntShowTm });

                                                        chart3.dataProvider = chartData3;
                                                        chart3.validateData();

                                                        chartData4 = [];
                                                        chartData4.push({ \"category\": \"CGV\",           \"value\": $A_SumShowSeat });
                                                        chartData4.push({ \"category\": \"롯데시네마\",    \"value\": $B_SumShowSeat });
                                                        chartData4.push({ \"category\": \"메가박스\",      \"value\": $C_SumShowSeat });
                                                        chartData4.push({ \"category\": \"기타\", \"value\": $D_SumShowSeat });

                                                        chart4.dataProvider = chartData4;
                                                        chart4.validateData();
                                                    }
                                                 ";
                                ?>
                                <td class="ty2"><?=number_format($A_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($A_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($A_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($A_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($B_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($B_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($B_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($B_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($C_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($C_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($C_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($C_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($D_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($D_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($D_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($D_SumShowSeat)?></td>
                                <td class="ty2"><?=number_format($All_CntScrnNm)?></td>
                                <td class="ty2"><?=number_format($All_SumScrnSeat)?></td>
                                <td class="ty2"><?=number_format($All_CntShowTm)?></td>
                                <td class="ty2"><?=number_format($All_SumShowSeat)?></td>
                                </tr>
                                <?

                                $Sum_A_CntScrnNm     += $A_CntScrnNm ;
                                $Sum_A_SumScrnSeat   += $A_SumScrnSeat ;
                                $Sum_A_CntShowTm     += $A_CntShowTm ;
                                $Sum_A_SumShowSeat   += $A_SumShowSeat ;
                                $Sum_B_CntScrnNm     += $B_CntScrnNm ;
                                $Sum_B_SumScrnSeat   += $B_SumScrnSeat ;
                                $Sum_B_CntShowTm     += $B_CntShowTm ;
                                $Sum_B_SumShowSeat   += $B_SumShowSeat ;
                                $Sum_C_CntScrnNm     += $C_CntScrnNm ;
                                $Sum_C_SumScrnSeat   += $C_SumScrnSeat ;
                                $Sum_C_CntShowTm     += $C_CntShowTm ;
                                $Sum_C_SumShowSeat   += $C_SumShowSeat ;
                                $Sum_D_CntScrnNm     += $D_CntScrnNm ;
                                $Sum_D_SumScrnSeat   += $D_SumScrnSeat ;
                                $Sum_D_CntShowTm     += $D_CntShowTm ;
                                $Sum_D_SumShowSeat   += $D_SumShowSeat ;
                                $Sum_All_CntScrnNm   += $All_CntScrnNm ;
                                $Sum_All_SumScrnSeat += $All_SumScrnSeat ;
                                $Sum_All_CntShowTm   += $All_CntShowTm ;
                                $Sum_All_SumShowSeat += $All_SumShowSeat ;
                            }
                            //echo "<br>".$MovieNm."(".$MovieCd.")";
                        }
                    }

                    ?>
                    <tr>
                    <td>합계</td>
                    <td class="ty2"><?=number_format($Sum_A_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_A_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_A_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_A_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_B_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_B_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_B_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_B_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_C_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_C_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_C_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_C_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_D_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_D_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_D_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_D_SumShowSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_All_CntScrnNm)?></td>
                    <td class="ty2"><?=number_format($Sum_All_SumScrnSeat)?></td>
                    <td class="ty2"><?=number_format($Sum_All_CntShowTm)?></td>
                    <td class="ty2"><?=number_format($Sum_All_SumShowSeat)?></td>
                    </tr>

                    </table>
                </div>


                <script type="text/javascript">
                    var chart;
                    var legend;

                    var chartData1 = [];
                    chartData1.push({ "category": "CGV",           "value": <?=$Sum_A_CntScrnNm?> });
                    chartData1.push({ "category": "롯데시네마",    "value": <?=$Sum_B_CntScrnNm?> });
                    chartData1.push({ "category": "메가박스",      "value": <?=$Sum_C_CntScrnNm?> });
                    chartData1.push({ "category": "기타", "value": <?=$Sum_D_CntScrnNm?> });

                    var chartData2 = [];
                    chartData2.push({ "category": "CGV",           "value": <?=$Sum_A_SumScrnSeat?> });
                    chartData2.push({ "category": "롯데시네마",    "value": <?=$Sum_B_SumScrnSeat?> });
                    chartData2.push({ "category": "메가박스",      "value": <?=$Sum_C_SumScrnSeat?> });
                    chartData2.push({ "category": "기타", "value": <?=$Sum_D_SumScrnSeat?> });

                    var chartData3 = [];
                    chartData3.push({ "category": "CGV",           "value": <?=$Sum_A_CntShowTm?> });
                    chartData3.push({ "category": "롯데시네마",    "value": <?=$Sum_B_CntShowTm?> });
                    chartData3.push({ "category": "메가박스",      "value": <?=$Sum_C_CntShowTm?> });
                    chartData3.push({ "category": "기타", "value": <?=$Sum_D_CntShowTm?> });

                    var chartData4 = [];
                    chartData4.push({ "category": "CGV",           "value": <?=$Sum_A_SumShowSeat?> });
                    chartData4.push({ "category": "롯데시네마",    "value": <?=$Sum_B_SumShowSeat?> });
                    chartData4.push({ "category": "메가박스",      "value": <?=$Sum_C_SumShowSeat?> });
                    chartData4.push({ "category": "기타", "value": <?=$Sum_D_SumShowSeat?> });

                    var chart1 = new AmCharts.AmPieChart();
                    var chart2 = new AmCharts.AmPieChart();
                    var chart3 = new AmCharts.AmPieChart();
                    var chart4 = new AmCharts.AmPieChart();

                    AmCharts.ready(function ()
                    {
                        // PIE CHART
                        chart1.dataProvider = chartData1;
                        chart1.titleField = "category";
                        chart1.valueField = "value";
                        chart1.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                        chart1.depth3D = 15;
                        chart1.angle = 30;
                        chart1.labelRadius = 5;
                        chart1.legend = {  "position": "right"  };
                        chart1.labelText = "[[percents]]%";
                        chart1.write("chartdiv1");

                        chart2.dataProvider = chartData2;
                        chart2.titleField = "category";
                        chart2.valueField = "value";
                        chart2.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                        chart2.depth3D = 15;
                        chart2.angle = 30;
                        chart2.labelRadius = 5;
                        chart2.legend = {  "position": "right"  };
                        chart2.labelText = "[[percents]]%";
                        chart2.write("chartdiv2");


                        chart3.dataProvider = chartData3;
                        chart3.titleField = "category";
                        chart3.valueField = "value";
                        chart3.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                        chart3.depth3D = 15;
                        chart3.angle = 30;
                        chart3.labelRadius = 5;
                        chart3.legend = {  "position": "right"  };
                        chart3.labelText = "[[percents]]%";
                        chart3.write("chartdiv3");

                        chart4.dataProvider = chartData4;
                        chart4.titleField = "category";
                        chart4.valueField = "value";
                        chart4.balloonText = "[[title]]<br><span style='font-size:14px'><b>[[value]]</b> ([[percents]]%)</span>";
                        chart4.depth3D = 15;
                        chart4.angle = 30;
                        chart4.labelRadius = 5;
                        chart4.legend = {  "position": "right"  };
                        chart4.labelText = "[[percents]]%";
                        chart4.write("chartdiv4");
                    });

                    function chgMovie(val)
                    {
                        <?=$chgMovie?>
                    }

                </script>
                <br>

                <select name="Loccation_" style="width:300px;" onchange="chgMovie(this.value);">
                     <option value="0">&nbsp;합계
                     <?=$optionMovie?>
                </select>
                <br><br>

                <div class="t3">
                    <table border="1">
                    <tr>
                        <th>스크린</th>
                        <th>좌석수</th>
                    </tr>
                    <tr>
                        <td><div id="chartdiv1" style="width: 600px; height: 400px;"></div></td>
                        <td><div id="chartdiv2" style="width: 600px; height: 400px;"></div></td>
                    </tr>
                    <tr>
                        <th>총회차</th>
                        <th>총좌석수</th>
                    </tr>
                    <tr>
                        <td><div id="chartdiv3" style="width: 600px; height: 400px;"></div></td>
                        <td><div id="chartdiv4" style="width: 600px; height: 400px;"></div></td>
                    </tr>
                    </table>
                </div>
                <?
            }
            ?>



<?
    }
?>
    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
