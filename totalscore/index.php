<?
    session_start();
?>
<!doctype html>
<html lang='kr'>
<head>
<? include "inc/Head.inc"; ?>

    <script type="text/javascript">
        function active_css()
        {
            $('#menu_home').attr("class","active");
        };

    </script>

    <title>토털스코어</title>
</head>
<body>
<?
    if  (!session_is_registered("logged_UserId"))
    {
        ?>
        <script type="text/javascript">

            alert('로그인을 해주세요!') ;

            location.href="http://www.mtns7.co.kr/" ;

        </script>
        <?
    }
    else
    {
        include "inc/Menu.inc";
    }
?>
</body>
<html>
