<?
     session_start();

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
             $('#menu2').attr("class","active has-sub");
        }

        $(document).ready(function()
        {
              var changEvent = function()
              {
                  $('<input>').attr(
                  {
                      type: 'hidden', name: 'Location',  value: $( "#Loccation" ).val()
                  }).appendTo('#formSelf');


                  $('<input>').attr(
                  {
                      type: 'hidden', name: 'Group1',  value: $("input:checkbox[id='Group1']").is(":checked")
                  }).appendTo('#formSelf');


                  $('<input>').attr(
                  {
                      type: 'hidden', name: 'Group2',  value: $("input:checkbox[id='Group2']").is(":checked")
                  }).appendTo('#formSelf');


                  $('<input>').attr(
                  {
                      type: 'hidden', name: 'Group3',  value: $("input:checkbox[id='Group3']").is(":checked")
                  }).appendTo('#formSelf');


                  $('<input>').attr(
                  {
                      type: 'hidden', name: 'Group4',  value: $("input:checkbox[id='Group4']").is(":checked")
                  }).appendTo('#formSelf');


                  $('<input>').attr(
                  {
                      type: 'hidden', name: 'Group5',  value: $("input:checkbox[id='Group5']").is(":checked")
                  }).appendTo('#formSelf');

                  $( "#formSelf" ).submit(); // 자기자신을 한번 더 호출한다.

                  //alert();
              };
              //changEvent();

              $( "#Loccation" ).change(function()
              {
                   changEvent();
              });

              //$( "input[type=checkbox]" ).on( "click", changEvent );
              $( "input.group:checkbox" ).on( "click", changEvent );
        }) ;


        function save_theather(theatherCd,theatherName,Location,Group)
        {
            var valLocation = $('select[name='+Location+']').val();
            var valGroup    = $(':radio[name="'+Group+'"]:checked').val();
            var valActive   = $('input:checkbox[name="Active_'+theatherCd+'"]').is(':checked');

            if  (valActive) active = 1 ; else active = 0 ;

            for (var i=0, len=Group.length; i<len; i++)
            {
                if ( Group[i].checked )
                {
                    val = Group[i].value;
                    break;
                }
            }
            $.post("fix_theather_ajax.php"
                   ,{ active       : active
                     ,theatherCd   : theatherCd
                     ,theatherName : theatherName
                     ,Location     : valLocation
                     ,Group        : valGroup
                    }
                   ,function(data)
                    {
                        if (data == "UPDATE")  alert("갱신이 완료되었습니다."); //
                        if (data == "INSERT")  alert("추가가 완료되었습니다."); //
                    });
        }

        function onclick_theather(divTheather)
        {
            $('#'+divTheather).toggle();
        }

        </script>

        <title>상영관리스트 보정/매핑</title>
    </head>
    <body>
<?
    if  (!session_is_registered("logged_UserId"))
    {
        ?>로그인을 해주세요!<?
    }
    else
    {
        include "inc/Menu.inc";
?>

        <form id="formSelf" action=<? echo htmlspecialchars($_SERVER["PHP_SELF"]); ?> method="post"></form>


        <?
        $sLoccation = $_POST["Location"];// echo $sLoccation ;

        $bGroup1 = $_POST["Group1"]; //echo "<br>".$bGroup1 ;
        $bGroup2 = $_POST["Group2"]; //echo "<br>".$bGroup2 ;
        $bGroup3 = $_POST["Group3"]; //echo "<br>".$bGroup3 ;
        $bGroup4 = $_POST["Group4"]; //echo "<br>".$bGroup4 ;
        $bGroup5 = $_POST["Group5"]; //echo "<br>".$bGroup5 ;
        ?>
        <select id="Loccation">
                <? if ($sLoccation=="")     $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="전체">&nbsp;전체
                <? if ($sLoccation=="서울") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="서울">&nbsp;서울
                <? if ($sLoccation=="경기강원") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="경기강원">&nbsp;경기강원
                <? if ($sLoccation=="대전충청") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="대전충청">&nbsp;대전충청
                <? if ($sLoccation=="대구경북") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="대구경북">&nbsp;대구경북
                <? if ($sLoccation=="부산경남") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="부산경남">&nbsp;부산경남
                <? if ($sLoccation=="광주호남") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="광주호남">&nbsp;광주호남
        </select>

        <label><input id="Group1" class="group" value="1" type="checkbox" <? if ($bGroup1=="true") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;CGV</label>
        <label><input id="Group2" class="group" value="2" type="checkbox" <? if ($bGroup2=="true") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;롯데</label>
        <label><input id="Group3" class="group" value="3" type="checkbox" <? if ($bGroup3=="true") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;메가박스</label>
        <label><input id="Group4" class="group" value="4" type="checkbox" <? if ($bGroup4=="true") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;프리머스</label>
        <label><input id="Group5" class="group" value="5" type="checkbox" <? if ($bGroup5=="true") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;그외</label>
        <br><br>

    <?
        $sQuery1 = "   SELECT a.Code          a_Code
                             ,a.TheatherName  a_TheatherName
                             ,a.Seat          a_Seat
                             ,b.Code          b_Code
                             ,COUNT(s.ScrnNm) s_CountScrnNm
                             ,SUM(s.Seat)     s_SumSeat
                             ,b.Location      b_Location
                             ,b.TheatherName  b_TheatherName
                             ,b.`Group`       b_Group
                             ,b.Active        b_Active
                         FROM kofic_theather a
                   INNER JOIN (select * from kofic_fix_theather where Active = 1) b
                           ON b.Code = a.Code
                    LEFT JOIN kofic_seat s
                           ON s.TheatherCd = a.Code
                        WHERE 1=1
                  ";

        $sQuery2 = "  GROUP BY a.Code
                              ,a.TheatherName
                              ,a.Seat
                              ,b.Code
                              ,b.Location
                              ,b.TheatherName
                              ,b.`Group`
                   ";
        if  (($sLoccation != "전체") && ($sLoccation != ""))  $sQuery1 .= "AND b.Location = '".iconv("UTF-8","EUC-KR", $sLoccation)."' " ;

        $tempQry = "";
        $tempQry1 = "";
        $tempQry2 = "";

        if  (($bGroup1 == "true") or ($bGroup2 == "true") or ($bGroup3 == "true") or ($bGroup4 == "true") or ($bGroup5 == "true"))
        {
            $tempQry1 = "AND ( ";
            $tempQry2 = ")     ";
        }

        if  ($bGroup1 == "true") {  if  ($tempQry != "")  $tempQry .= " OR ";  $tempQry .= " b.`Group` = "."1"." ";  }
        if  ($bGroup2 == "true") {  if  ($tempQry != "")  $tempQry .= " OR ";  $tempQry .= " b.`Group` = "."2"." ";  }
        if  ($bGroup3 == "true") {  if  ($tempQry != "")  $tempQry .= " OR ";  $tempQry .= " b.`Group` = "."3"." ";  }
        if  ($bGroup4 == "true") {  if  ($tempQry != "")  $tempQry .= " OR ";  $tempQry .= " b.`Group` = "."4"." ";  }
        if  ($bGroup5 == "true") {  if  ($tempQry != "")  $tempQry .= " OR ";  $tempQry .= " ( b.`Group` <> '1' and b.`Group` <> '2' and b.`Group` <> '3' and b.`Group` <> '4' )    ";  }

        $sQuery = $sQuery1 . $tempQry1 . $tempQry . $tempQry2 . $sQuery2 ;

        //$sQuery .= "ORDER BY b.Location, b.TheatherName " ;
        $sQuery .= "ORDER BY b.`Group`, b.Location, b.TheatherName " ;

    //echo "<br>".iconv("EUC-KR", "UTF-8",$sQuery); ;

        ?>

        <div class="t3" >
            <table border="1">
            <tr>
                <th>구분</th>
                <!--th>코드</th-->
                <th>활성화</th>
                <th>극장명</th>
                <th>총좌석수</th>
                <th>상영관</th>
                <th>지역</th>
                <th>그룹</th>
                <th></th>
            </tr>
            <?
            $QryKoficTheather = mysql_query($sQuery,$connect) ;
            while  ($ArrKoficTheather = mysql_fetch_array($QryKoficTheather))
            {
                $sBThearterCode     = $ArrKoficTheather["b_Code"] ; // 추가되는 경우는 값이 null
                $sThearterCode      = $ArrKoficTheather["a_Code"] ;
                $sTheatherName      = $ArrKoficTheather["a_TheatherName"] ;
                $sSeat              = $ArrKoficTheather["a_Seat"] ;
                $sCountScrnNm       = $ArrKoficTheather["s_CountScrnNm"] ;
                $sSumSeat           = $ArrKoficTheather["s_SumSeat"] ;
                $sThearterLoccation = $ArrKoficTheather["b_Location"] ;
                $sThearterGroup     = $ArrKoficTheather["b_Group"] ;
                $sActive            = $ArrKoficTheather["b_Active"] ;

    //if  (($sThearterCode != "001111")) continue;
                $sTheatherName      = iconv("EUC-KR", "UTF-8",$sTheatherName);
                $sThearterLoccation = iconv("EUC-KR", "UTF-8",$sThearterLoccation);

                if  ($sBThearterCode == null)  $style = "bgcolor=\"#f0e0e0\"" ; else  $style = "" ;
                ?>
                <tr <?=$style?>>
                    <td>
                        <?if  ($sBThearterCode == null) echo "미지정";?>
                    </td>
                    <!--td>
                        <b><a id="aTheather_<?=$sThearterCode?>" href="#aTheather_<?=$sThearterCode?>" onclick="onclick_theather('divTheather_<?=$sThearterCode?>')"> <?=$sThearterCode?></a></b>
                    </td-->
                    <td class="ty5">
                        <?

                        if  ($sActive == "1")   $checked = "checked" ;
                        else                    $checked = "" ;
                        ?>
                        <input type="checkbox" name="Active_<?=$sThearterCode?>" <?=$checked?> value="1">
                    </td>
                    <td>
                        <input type="text" value="<?=$sTheatherName?>"  size="40">
                    </td>
                    <td class="ty2">
                        <? if  ($sSeat!='-1') echo number_format($sSeat); else echo "좌석수없음" ?>
                    </td>
                    <?
                    if  ($sSeat != $sSumSeat)  $style = "style=\"font-color:red;\"" ;
                    else                       $style = "" ;

                    ?>
                    <td class="ty2" <?=$style?>>
                        <?=number_format($sCountScrnNm)?>(<?=number_format($sSumSeat)?>)
                    </td>

                    <td>
                        <select name="Loccation_<?=$sThearterCode?>">
                            <? if ($sThearterLoccation=="")         $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="">&nbsp;
                            <? if ($sThearterLoccation=="서울")     $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="서울">&nbsp;서울
                            <? if ($sThearterLoccation=="경기강원") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="경기강원">&nbsp;경기강원
                            <? if ($sThearterLoccation=="대전충청") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="대전충청">&nbsp;대전충청
                            <? if ($sThearterLoccation=="대구경북") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="대구경북">&nbsp;대구경북
                            <? if ($sThearterLoccation=="부산경남") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="부산경남">&nbsp;부산경남
                            <? if ($sThearterLoccation=="광주호남") $selected = "selected=selected" ; else $selected = "" ; ?><option <?=$selected?> value="광주호남">&nbsp;광주호남
                        </select>
                    </td>
                    <td>
                        <label><input name="Group_<?=$sThearterCode?>" value="1" type="radio" <? if ($sThearterGroup=="1") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;CGV</label>
                        <label><input name="Group_<?=$sThearterCode?>" value="2" type="radio" <? if ($sThearterGroup=="2") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;롯데</label>
                        <label><input name="Group_<?=$sThearterCode?>" value="3" type="radio" <? if ($sThearterGroup=="3") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;메가박스</label>
                        <label><input name="Group_<?=$sThearterCode?>" value="4" type="radio" <? if ($sThearterGroup=="4") $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;프리머스</label>
                        <label><input name="Group_<?=$sThearterCode?>" value=""  type="radio" <? if ($sThearterGroup=="")  $checked = "checked" ; else $checked = "" ; ?> <?=$checked?> />&nbsp;그외</label>
                    </td>
                    <td>
                        <input type="button" onclick="save_theather('<?=$sThearterCode?>','<?=$sTheatherName?>','Loccation_<?=$sThearterCode?>','Group_<?=$sThearterCode?>');" value="<?if ($sBThearterCode == null) {?>추가<?} else {?>저장<?}  ?>">
                    </td>
                </tr>
                <?
            }
            ?>
            </table>
        </div>
<?
    }
?>


    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>
