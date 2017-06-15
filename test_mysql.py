import sys
import pymysql
# import getpass

# print(sys.stdin.encoding)

userid = 'mtns_kofic' # input('사용자 아이디 : ')
userpw = '' # input('사용자 암호 : ') # todo
# userpw = getpass.getpass('사용자 암호 : ')


try:
    conn = pymysql.connect( host='xxxxx.co.kr', port=3306, user=userid, passwd=userpw, db='mtns_kofic', charset='utf8' )
except Exception  as error: # pymysql.InternalError
    code, message = error.args
    print("접속오류 : ", code, message)
else:
    try:
        with conn.cursor() as cur:
            sql = 'SHOW TABLES WHERE Tables_in_mtns_kofic = %s'
            nrow = cur.execute( sql, ('test',) ) # 레코드 갯수를 리턴한다.

        if nrow == 0: # 없다면...
            with conn.cursor() as cur:
                sql = 'CREATE TABLE test ( ttt varchar(300) NOT NULL ) ENGINE=MyISAM DEFAULT CHARSET=euckr'
                cur.execute( sql )

            with conn.cursor() as cur:
                sql = "INSERT INTO test VALUES ('한글');"
                cur.execute( sql )

    except Exception  as error:  #
        code, message = error.args
        print( "실행오류 : ", code, message )

finally:
    conn.close()
    print('실행완료!!')
