'''

megabox

http://www.megabox.co.kr/

'''
import re
import json
from jsonpath_rw import jsonpath, parse  # pip install jsonpath-rw      https://pypi.python.org/pypi/jsonpath-rw
from urllib.request import urlopen
from bs4 import BeautifulSoup
import requests # http://docs.python-requests.org/en/master/user/quickstart/ pip install requests
from multiprocessing import Queue # python Setup.py build # exe 파일 생성을 위해 꼭 필요

#------------------------------------------------------------------------------------------------------------------------------------------------------
# 공통 변수......
#
dicMovies = {}   # 영화 코드 정보
dicRegions = {}  # 지역코드 정보
dicCinemas = {}  # 극장코드 정보

dicTicketingData = {}  # 티켓팅 정보

#
#
#


#########################################################################################################################################
# 영화(http://www.megabox.co.kr/?menuId=movie) 에서 영화데이터를 가지고 온다. (dicMovies)
#
def crawl_mega_movie():
#
#

    url = 'http://www.megabox.co.kr/pages/movie/Movie_List.jsp'
    fields = {"menuId": "movie"
            , "startNo": "0"
            , "count": "100"
            , "sort": "releaseDate"
              }
    r = requests.post( url, fields )
    # print( r.text )

    soup = BeautifulSoup( r.text, 'html.parser' )

    tags1 = soup.select( "li.item > div.front-info" )
    for tag1 in tags1:
        # print( tag1 )
        # print( '-------------------------------------------------------------' )

        releasedate = ''
        moviegbn = ''
        moviename = ''

        tags2 = tag1.select( "div.d_day" )
        for tag2 in tags2:
            releasedate = tag2.text.strip().split(' ')
            releasedate = releasedate[0][0:4] + releasedate[0][5:7] + releasedate[0][8:10]
            # print( releasedate )

        tags2 = tag1.select( "div.movie_info > h3.sm_film > span.film_rate" )
        for tag2 in tags2:
            moviegbn = tag2.text.strip()
            # print( moviegbn )


        tags2 = tag1.select( "div.movie_info > h3.sm_film > a.film_title" )
        for tag2 in tags2:
            moviename = tag2.text.strip()
            # print( moviename )

            moviecode = ''
            r = re.compile( '\d+' )
            results = r.findall( tag2['onclick'] )
            for result in results:
                moviecode = result
            # print( moviecode )

            dicMovies[moviecode] = [releasedate, moviegbn, moviename]  # 영화데이터 정보
#
# def func_mega_movie():
#


#------------------------------------------------------------------------------------------------------------------------------------------------------
# 영화관에서 지역데이터,영화관데이터를 가지고 온다. (dicRegions,dicCinemas)
#
def func_mega_cinema():
    url = urlopen( "http://www.megabox.co.kr/?menuId=theater" )
    data = url.read().decode( 'utf-8' )
    # print(data)

    soup = BeautifulSoup( data, 'html.parser' )

    tags1 = soup.select( "div.content_wrap > ul > li" )
    for tag1 in tags1:
        # print(tag1)

        tags2 = tag1.select( "a" )
        for tag2 in tags2:
            if tag2.text=='선호영화관':
                continue

            dicRegions[tag2['data-region']] = tag2.text # 지역코드 저장

        # print('-------------')

    for dicRegion in dicRegions:
        # print( '{} {}'.format( dicRegion, dicRegions[dicRegion] ) )

        url = 'http://www.megabox.co.kr/DataProvider'
        fields = {"_command": 'Cinema.getCinemasInRegion'
                    ,"siteCode": '36'
                    ,"areaGroupCode": dicRegion
                    ,"reservationYn": 'N'
                  }
        r = requests.post( url, fields )

        json_obj = r.json()
        # print(json_obj)

        jsonpath_expr = parse( 'cinemaList[*]' )

        i = 0
        for match in jsonpath_expr.find( json_obj ):
            cinemaname    = str( match.value['cinemaName'] )
            cinemacode    = str( match.value['cinemaCode'] )
            kofcinemacode = str( match.value['kofCinemaCode'] )

            # print('{} {}'.format(cinemacode,cinemaname))
            dicCinemas[cinemacode] = [dicRegion, cinemaname, kofcinemacode]
#
# def func_mega_cinema():
#



# ------------------------------------------------------------------------------------------------------------------------------------------------------
# 영화관에서 영화관에 스케줄데이터를 가지고 온다. (dicRegions,dicCinemas)
#
def func_mega_schedule():

    for count in range( 0, 3 ):  # 3d일간

        dicPlaydate = {}

        for dicCinema in dicCinemas:
            # print( str( count ) )

            # if dicCinema != '6902':  continue # 제주아라....

            dicSchMovies = {}    # 스케쥴 > 극장 / 영화 정보
            dicSchRooms = {}     # 스케쥴 > 관 정보
            dicSchMovRooms = {}  # 스케쥴 > 극장 / 영화 / 관 정보

            url = 'http://www.megabox.co.kr/pages/theater/Theater_Schedule.jsp'
            fields = {"cinema": dicCinema, "count": count}

            r = requests.post( url, fields )
            # print(r.text)

            soup = BeautifulSoup( r.text, 'html.parser' )
            # print( '-------------------------------------------------------------' )

            tags1 = soup.select( "input#playDate" )
            for tag1 in tags1:
                val = [v[0:4] + v[5:7] + v[8:10]  for k,v in tag1.attrs.items() if (k=='value')]
                playdate = val[0]
                # print(playdate)


            moviecode = ''
            moviegbn = ''
            moviename = ''
            cinemaroom = ''
            moviegubun = ''

            noRooms = 0
            cntRoom = 1
            tags1 = soup.select( "table.movie_time_table > tr.lineheight_80" )
            for tag1 in tags1:
                # print(tag1)

                noRooms = noRooms + 1

                tags2 = tag1.select( "th.title > div > span.age_m" )
                for tag2 in tags2:
                    # print(tag2.attrs.values())
                    if tag2.text != '':
                        moviegbn = tag2.text
                        # print( tag2 ) # 15세 관람가

                tags2 = tag1.select( "th.title > div > strong" )
                for tag2 in tags2:
                    if tag2.text == '\xa0':
                        cntRoom = cntRoom + 1 # 같은 영화가 반복 관이 추가
                    else:
                        cntRoom = 1
                        moviename = tag2.text
                        # print( tag2 ) # 겟 아웃


                tags2 = tag1.select( "th.room > div" )
                for tag2 in tags2:
                    cinemaroom = tag2.text
                    # print( tag2 ) # 4관

                tags2 = tag1.select( "th.room > small" )
                for tag2 in tags2:
                    moviegubun = tag2.text
                    # print( tag2 ) # 디지털(자막)

                dicHoverTime = {} #
                tags2 = tag1.select( "td > div.cinema_time" )
                for tag2 in tags2:

                    time = ''
                    type = ''
                    seat = ''

                    tags3 = tag2.select( "a" )
                    # print( tags3)

                    if len( tags3 ) > 0:   # print( '일반' )

                        for tag3 in tags3:
                            onclick = tag3['onclick']
                            # hrefs = href.split( '=' )
                            # print(onclick)
                            moviecode = onclick[24:30]

                            tags4 = tag3.select( "span.hover_time" )
                            # print(tags4)
                            for tag4 in tags4:
                                times = tag4.text.split( '~' )

                                dicHoverTime[times[0]] = [times[1], ]  ##############################

                    else:  # print( '예매마감' )

                        tags3 = tag2.select( "p.time_info" )
                        for tag3 in tags3:
                            tags4 = tag3.select( "span.time" )
                            for tag4 in tags4:
                                times = [tag4.text, '']
                                # print( tag4 )

                            tags4 = tag3.select( "span.seat" )
                            for tag4 in tags4:
                                seat = tag4.text # '예매마감'
                                # print( tag4 )

                        dicHoverTime[times[0]] = [times[1], '예매마감', seat ]  ##############################

                    tags3 = tag2.select( "p.time_info" )
                    for tag3 in tags3:
                        tags4 = tag3.select( "span.type" )
                        for tag4 in tags4:
                            type = tag4.text
                            # print( tag4 )

                        tags4 = tag3.select( "span.time" )
                        for tag4 in tags4:
                            time = tag4.text
                            # print( tag4 )

                        tags4 = tag3.select( "span.seat" )
                        for tag4 in tags4:
                            seat = tag4.text
                            # print( tag4 )

                        dicHoverTime[times[0]].append( type )
                        dicHoverTime[times[0]].append( seat )

                dicSchRooms[noRooms] = [moviecode, moviename, moviegbn, cntRoom, cinemaroom, moviegubun, dicHoverTime]  ####
                # print(cntRoom,moviename)

            # 영화별로 추려내고
            old_moviecode = ''
            for k, v in dicSchRooms.items():
                if old_moviecode != str( v[0] ):
                    dicSchMovies[str( v[0] )] = [v[1], v[2]]  #### playdate, moviename, moviegbn 만 이동..
                    old_moviecode = str( v[0] )

            # 다시 영화별/관별 로 loop를 돌아.. dicSchMovRooms생성..
            for dicSchMovie in dicSchMovies:
                for k, v in dicSchRooms.items():
                    if dicSchMovie == v[0] : # moviecode
                        dicSchMovRooms[k] = [dicSchMovie, v[3], v[4], v[5], v[6]]

            for dicSchMovie in dicSchMovies:
                dictmp = {} # 관별 시간표 임시 dictionary
                for k, v in dicSchMovRooms.items():
                    if v[0] == dicSchMovie:
                        dictmp[k] = v
                # print(dictmp )

                dicSchMovies[dicSchMovie].append(dictmp)

            dicPlaydate[dicCinema] = dicSchMovies

         # for dicCinema in dicCinemas:

        dicTicketingData[playdate] = dicPlaydate

    # for count in range( 0, 3 ):  # 3d일간

    # print(dicTicketingData)

#
#   def func_mega_cinema():
#





def func_mega_upload():

    fields = { "movies": str( dicMovies )
             , "regions": str( dicRegions )
             , "cinemas": str( dicCinemas )
             , "ticketingdata": str( dicTicketingData )
             }
    url = 'http://www.mtns7.co.kr/totalscore/mega_upload.php'

    r = requests.post( url, fields )

    # print( '[',r.text,']' )
#

if  __name__ == '__main__':

    crawl_mega_movie()
    func_mega_cinema()
    func_mega_schedule()

    # print( '-------------------------------------------------------------dicRegions' )
    # for k, v in dicRegions.items():
    #     print( '{} {}'.format( k, v ) )
    # print( '-------------------------------------------------------------' )
    #
    # print( '-------------------------------------------------------------dicMovies' )
    # for k, v in dicMovies.items():
    #     print( '{} {}'.format( k, v ) )
    # print( '-------------------------------------------------------------' )
    #
    # print( '-------------------------------------------------------------dicSchRoom' )
    # for k, v in dicSchRooms.items():
    #     print( '{} {}'.format( k, v ) )
    # print( '-------------------------------------------------------------' )
    #
    # print( '-------------------------------------------------------------dicCinemas' )
    # for k, v in dicCinemas.items():
    #     print( '{} {}'.format( k, v ) )


    func_mega_upload()

#
#
#
