<?
    header("Pragma: no-cache");
    header("Cache-Control: no-cache,must-revalidate");

    session_start();
    set_time_limit(0) ;

    include "inc/config.php";       // {[데이터 베이스]} : 환경설정
    $connect = dbconn() ;           // {[데이터 베이스]} : 연결
    mysql_select_db($cont_db) ;     // {[데이터 베이스]} : 디비선택

?>
<!DOCTYPE html>
<html lang="kr">
    <head>

<? include "inc/Head.inc"; ?>

        <script type="text/javascript">
        function active_css()
        {
            $('#menu1').attr("class","active has-sub");
        };
        </script>

        <script type="text/javascript">
        $(document).ready(function()
        {
            $('#iframe').load(function ()
            {
                var height = $(document).height() - 200;
                //alert();
                this.style.height = height + 'px';
            });
        });

        function Message(data)
        {
            var jbSplit = data.split( '|' );

            $('#type').text(jbSplit[0]);
            $('#status').text(jbSplit[1]);
            $('#percent').text(jbSplit[2]+' % ');
        }

        function getTimeStamp()
        {
            var d = new Date();

            var s =
              leadingZeros(d.getFullYear(), 4) + '-' +
              leadingZeros(d.getMonth() + 1, 2) + '-' +
              leadingZeros(d.getDate(), 2) + ' ' +

              leadingZeros(d.getHours(), 2) + ':' +
              leadingZeros(d.getMinutes(), 2) + ':' +
              leadingZeros(d.getSeconds(), 2);

            return s;
        }


        function leadingZeros(n, digits)
        {
            var zero = '';
            n = n.toString();

            if (n.length < digits) {
              for (i = 0; i < digits - n.length; i++)
                zero += '0';
            }
            return zero + n;
        }

        function StopTimer()
        {
            clearInterval(timer); // 타이머 정지

            //$('#iframe').attr('src','crawling_All_Blank.php');

            subject = getTimeStamp() ;
            body    = $('#iframe').contents().find('body').html();

            /* 완료 메일 전송 !!! */
            $.post("lib/Sendmail_ajax.php"
                   ,{
                        subject : subject,
                        data    : body
                    }
                   ,function(data)
                    {
                        //alert(date);
                    }
                  );

            alert(subject);
        }

        var timer ;
        var dStart ;
        var dCur ;
        var gap ;

        var oldgap = -1;


        function counting()
        {
            dCur = new Date();
            gap = Math.round((dCur - dStart) / 1000) ;

            if  (oldgap != gap)
            {
                oldgap = gap;

                var minute = parseInt(gap / 60) ; // 분
                var second = gap % 60 ;           // 초

                if  (minute > 0) { $('#timer').text(minute+'분 '+second+'초'); }
                else             { $('#timer').text(             second+'초'); }
            }
        }


        // 타임 이벤트 경과 시간 출력, crawliing_All_ajax_status.php호출 상태출력
        var fAct1 = function action()
        {
            counting();

            clearInterval(timer); // 타이머 정지

            $.post("crawliing_All_ajax_status.php"
                   ,{}
                   ,function(data)
                    {
                        //console.log("crawliing_All_ajax_status.php한글 : "+data);

                        var jbSplit = data.split( '|' );

                        $('#type').text(jbSplit[0]);
                        $('#status').text(jbSplit[1]);
                        $('#percent').text(jbSplit[2]);

                        timer = setInterval(fAct1,1000); // 타이머 재시작
                    }
                  );
        }

        var fAct2 = function action()
        {
            counting();
        }

        // 시작버튼 클릭  타이머가동, crawliing_All_ajax_action.php 호출(크로링..)
        function start_action()
        {
            timer = setInterval(fAct2,1000);

            dStart = new Date();

            //console.log("crawliing_All_ajax_action.php");

            $('#iframe').attr('src','crawling_All_iFrame.php'); // http://www.mtns7.co.kr/totalscore/crawling_All_iFrame.php

            /*
            $.post("crawliing_All_ajax_action.php"
                   ,{}
                   ,function(data)
                    {
                        $('#display').html(data);

                        clearInterval(timer); // 타이머 정지
                        timer = setInterval(fAct2,1000);

                        $.post("crawliing_All_ajax_boxoffice.php"
                               ,{}
                               ,function(data)
                                {
                                    $('#display').html(data);

                                    clearInterval(timer); // 타이머 정지
                                    alert("끝!");

                                }
                              );
                    }
                  );
            */
        }

        </script>


        <title>상영관리스트 가져오기</title>
    </head>
    <body>
    <?
    if  (!session_is_registered("logged_UserId"))
    {
        echo "로그인을 해주세요!";
    }
    else
    {
        include "inc/Menu.inc";
        ?>
        <table>
        <tr>
            <td><input type="button" onclick="start_action()" value="start!!!"></td>
            <td><div id="timer"></div></td>
        </tr>
        </table>
        <br>

        <div class="t3" >
            <table border="1" width="100%">
            <tr>
                <th width="100px">종류</th>
                <th>내용</th>
                <th width="100px">경과</th>
            </tr>
            <tr>
                <td><div id="type">&nbsp;</div></td>
                <td><div id="status">&nbsp;</div></td>
                <td class="ty2" ><div id="percent">&nbsp;</div></td>
            </tr>
            </table>
        </div>

        <!--table width="90%">
          <tr>
            <td width="100%"><textarea width="100%">.</textarea></td>
          </tr>
          <tr>
            <td><div id="display"></div></td>
          </tr>
        </table-->

        <iframe id="iframe" width="100%" src="crawling_All_Blank.php"></iframe>

        <?
    }
    ?>
    </body>
</html>

<?
    mysql_close($connect) ;      // {[데이터 베이스]} : 단절
?>

