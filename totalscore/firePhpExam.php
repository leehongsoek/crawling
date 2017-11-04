<?

require('FirePHPCore/FirePHP.class.php');

ob_start();

$firephp = FirePHP::getInstance(true);

$firephp->fb('이런 씨바쎄끼....'); // Defaults to FirePHP::LOG
$firephp->fb('log 좃까라..!'  ,FirePHP::LOG);
$firephp->fb('Info 좃까라..!' ,FirePHP::INFO);
$firephp->fb('Warn 좃까라..!' ,FirePHP::WARN);
$firephp->fb('Error 좃까라..!',FirePHP::ERROR);

$firephp->fb('좃까라..! with label','레이블',FirePHP::LOG);

$firephp->fb(array('key1'=>'val1',
                   'key2'=>array(array('v1','v2'),'v3')
                  )
             ,'배열'
             ,FirePHP::LOG
             );

function test($Arg1)
{
  throw new Exception('Test 예외!!');
}

try
{
  test(array('Hello'=>'World'));
}
catch(Exception $e)
{
  $firephp->fb($e); // 예외 로그 스텍 추적과 변수들
}

$firephp->fb('백트레이스 to 여기',FirePHP::TRACE);

$firephp->fb(array('abC'
                  ,array(array('SQL Statement'
                              ,'Time'
                              ,'Result')
                        ,array('SELECT * FROM Foo'
                              ,'0.02'
                              ,array('row1','row2')
                              )
                        ,array('SELECT * FROM Bar'
                              ,'0.04'
                              ,array('row1','row2')
                              )
                        )
                  )
            ,FirePHP::TABLE
            );

// Will show only in "Server" tab for the request ??
$firephp->fb(apache_request_headers(),'RequestHeaders',FirePHP::DUMP);


print 'Hello World';

?>