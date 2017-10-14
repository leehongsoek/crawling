'''

CGV

http://www.cgv.co.kr/

'''

import logging
import sys
import datetime, time
from bs4 import BeautifulSoup
# ChromeDriver 다운로드 사이트 (https://sites.google.com/a/chromium.org/chromedriver/downloads)
from selenium import webdriver # pip install selenium
import urllib3  # pip install urllib3
import json

dicMovies = {}   # 영화 코드 정보
dicRegions = {}  # 지역코드 정보
dicTheaters = {} # 극장코드 정보
dicTicketingData = {}  # 티켓팅 정보

http = urllib3.PoolManager()

#########################################################################################################################################
# 영화/무비차트(http://www.cgv.co.kr/movies/?ft=0) 애서 영화정보를 가지고온다. (dicMovies)
#
def crawl_cgv_moviechart():

    print( '### 영화/무비차트(http://www.cgv.co.kr/movies/) ###' )

    # pipe logs to stdout
    logger = logging.getLogger()
    logger.addHandler( logging.StreamHandler( sys.stdout ) )
    logger.setLevel( logging.NOTSET )

    driver = webdriver.Chrome( 'C:/chromedriver_win32/chromedriver.exe' ) # 다운받은 파일을 압축푼 후 실행파일을 해당경로에 푼다.....
    driver.implicitly_wait( 3 )

    driver.get( 'http://www.cgv.co.kr/movies/' )
    driver.implicitly_wait( 3 )

    driver.find_element_by_xpath( '//*[@class="btn-more-fontbold"]' ).click() # '더보기' 클릭
    driver.implicitly_wait( 3 )

    time.sleep( 2 ) # 초 단위 지연...

    html = driver.page_source # 패이지 소스를 읽어온다.....
    driver.quit()

    soup = BeautifulSoup( html )

    print( '--------------------------------------------------' )
    tags1 = soup.select( "div.sect-movie-chart > ol > li" )  ## 영화 리스트 순환단위
    for tag1 in tags1:
        #print( tag1 )

        moviecode = '' # 영화코드
        grade = '' # 관람등급
        rank = '' # 순위
        moviename = '' # 영화명
        percent = '' # 예매율
        releasedate = '' # 개봉일
        open_type = '' # 개봉종류

        tags2 = tag1.select( "div.box-image > a" )  # 영화코드
        for tag2 in tags2:
            #movecode = tag2.text.strip()
            href = tag2['href']
            hrefs = href.split( '?' )

            if (len( hrefs ) == 2):
                midxs = hrefs[1].split( '=' )

                if (len( midxs ) == 2):
                    moviecode = midxs[1]

        tags2 = tag1.select( "span.ico-grade" ) # 관람등급
        for tag2 in tags2:
            grade = tag2.text.strip()
            #print(grade)



        tags2 = tag1.select( "strong.rank" )  # 순위
        for tag2 in tags2:
            rank = tag2.text.strip()
            # print(rank)

        tags2 = tag1.select( "strong.title" )  # 영화명
        for tag2 in tags2:
            moviename = tag2.text.strip()
            # print(title)

        tags2 = tag1.select( "strong.percent > span" )  # 예매율
        for tag2 in tags2:
            percent = tag2.text.strip()
            # print(percent)

        tags2 = tag1.select( "span.txt-info" )  # 개봉일
        for tag2 in tags2:
            tags3 = tag2.select( "span" )
            for tag3 in tags3:
                open_type = tag3.text.strip()
                tag3.extract()  # 자식태그를 제거한다.
            #print(open_type)


            releasedate = tag2.text.strip()
            # print(opened)

        print( '{0}, {1}, {2}, {3}, {4}, {5}, {6}'.format( moviecode, grade, rank, moviename, percent, releasedate, open_type ) )
        dicMovies[moviecode] = [moviename, releasedate]  # 영화데이터 정보
#
#
#


#########################################################################################################################################
# 영화/무비파인더(http://www.cgv.co.kr/movies/finder.aspx) 에서 영화데이터를 가지고 온다. (dicMovies)
#
def crawl_cgv_moviefinder():

    print( '### 영화/무비파인더(http://www.cgv.co.kr/movies/finder.aspx) ###' )

    mov_count = 0

    date1 = datetime.date.today()                    ## 오늘자 날짜객체
    date2 = date1 + datetime.timedelta( days=-365 )  ## 1년전

    year_from = date2.year ## 1년전 개봉작부터...

    print( '-------------------------------------' )
    print( 'no, 코드, 영화명, 개봉일자' )
    print( '-------------------------------------' )

    # 1 ~ 페이지 에서 부터 영화정보 (코드+이름+개봉일) 를 가지고 온다...
    i = 0
    while True:

        i = i + 1

        # if i != 1:       # 일단 하나만 가지고 온다.
        #     continue

        url = 'http://www.cgv.co.kr/movies/finder.aspx?s=true&sdate='+str(year_from)+'&edate=2020&page='+str(i) # 무비파인더 에서 영화 리스트

        #print(url)

        r = http.request( 'GET', url )

        data = r.data.decode( 'utf-8' )
        # print(data)

        soup = BeautifulSoup( data, 'html.parser' )

        # 아래의 선택조건에 해당하는 영화가 총 0건 검색되었습니다. 를 체크
        find_num = 0
        tags1 = soup.select( "h3.sub > span > strong > i" )
        for tag1 in tags1:
            find_num = tag1.text.strip()
            #print( find_num )

        if find_num == '0': # 아래의 선택조건에 해당하는 영화가 총 0건 검색되었습니다.
            break

        tags1 = soup.select( "div.sect-search-chart > ol" )
        for tag1 in tags1:


            #print( tag1 )

            tags2 = tag1.select( "li" )
            for tag2 in tags2:
                #print( tag2 )

                moviecode = ''
                moviename = ''
                releasedate = ''

                # 페이지마다 아래 테그가 추가되므로 style이 없는 건만 파싱한다.
                # <li style="width:100%;text-align:center;padding:40px 0 40px 0;display:none">검색결과가 존재하지 않습니다.</li>

                style = str(tag2.get('style'))
                #print('style = ' + style)
                if style == 'None':

                    tags3 = tag2.select( "div.box-contents > a" )
                    for tag3 in tags3:
                        href = tag3['href']
                        hrefs = href.split( '=' )

                        moviecode = hrefs[1]
                        moviename = tag3.text.strip()
                        # print( '{} {}'.format([moviecode, moviename]) )


                    tags3 = tag2.select( "span.txt-info" )
                    for tag3 in tags3:
                        # for lin in tag3.text.splitlines():
                        #     print( ' +{}+ '.format(lin.strip()) )
                        releasedate = tag3.text.splitlines()[2].strip()
                        if releasedate != '개봉예정':
                            releasedate = releasedate[0:4] + releasedate[5:7] + releasedate[8:10]
                        else:
                            releasedate = ''

                            #print( ' +{}+ '.format( releasedate ) )

                    mov_count += 1

                    dicMovies[moviecode] = [moviename, releasedate]  # 영화데이터 정보
                    print( '{} : {},{},{}'.format( mov_count, moviecode, moviename, releasedate ) )
#
# 영화/무비파인더(http://www.cgv.co.kr/movies/finder.aspx) 에서 영화데이터를 가지고 온다. (dicMovies)
#



#########################################################################################################################################
# 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/) 극장정보를 가지고 온다. (dicTheaters)
#
def crawl_cgv_theaters():
    
    print( '### 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/) ###' )

    theater_count = 0

    url = 'http://www.cgv.co.kr/reserve/show-times/'
    r = http.request( 'GET', url )

    data = r.data.decode( 'utf-8' )
    # print(data)

    print( '-------------------------------------' )
    print( 'no, 코드, 지역명, 극장명' )
    print( '-------------------------------------' )

    data_lines = data.splitlines()

    for data_line in data_lines:

        jsondata      = 'theaterJsonData = ' # 지역별 극장전체 정보를 가지고 있는 json 변수
        len_jsondata  = len(jsondata)
        find_jsondata = data_line.find( jsondata )

        if find_jsondata != -1: # 발견하면...

            json_txt = data_line[find_jsondata + len_jsondata:].split( ';' ) #print( json_txt[0] )

            json_obj = json.loads( str(json_txt[0]) ) # text json 을 json 객체로 변환
            for json_theater in json_obj:

                # print( json_theater['DisplayOrder'] ) # 출력 순서
                # print( json_theater['RegionCode'] ) # 지역코드
                # print( json_theater['RegionName'] ) # 지역
                regioncode = json_theater['RegionCode']
                regionname = json_theater['RegionName']

                regioncodes = regioncode.split( ',' )
                regionnames  = regionname.split( '/' )
                # print(str(len(regionnames)))

                ## 복합지역인 경우는 개별 분리한다.
                i = 0
                for regioncode in regioncodes:
                    dicRegions[regioncode] = regionnames[i];   # 지역코드 정보 추가 (지역코드+지역명)
                    i = i + 1


                for theater in json_theater['AreaTheaterDetailList']:
                    # print(theater)
                    regioncode  = theater['RegionCode']  # 극장지역코드
                    theatercode = theater['TheaterCode'] # 극장코드
                    theatername = theater['TheaterName'] # 극장면

                    theater_count += 1

                    dicTheaters[theatercode] = [regioncode, dicRegions[regioncode], theatername]  # 극장코드 정보 추가 (지역코드+지역명+극장명)
                    print( '{} : {},{},{}'.format( theater_count, theatercode, dicRegions[regioncode], theatername ) )


    region_count = 0
    print( '-------------------------------------' )
    print( 'no, 코드, 지역명' )
    print( '-------------------------------------' )

    for region in dicRegions:

        region_count += 1

        print('{} : {},{}'.format(region_count,region,dicRegions[region]))
#
# 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/) 극장정보를 가지고 온다. (dicTheaters)
#



#########################################################################################################################################
# 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/)의 프래임에서 상영정보를 가지고 온다. (dicTicketMovies)
#
def crawl_cgv_showtimes():

    days = []

    date1 = datetime.date.today()                 ## 오늘자 날짜객체
    date2 = date1 + datetime.timedelta( days=1 )  ## +1 일
    date3 = date2 + datetime.timedelta( days=1 )  ## +2 일

    days.append( '{:04d}{:02d}{:02d}'.format( date1.year, date1.month, date1.day ) )  ## 오늘의 날짜
    days.append( '{:04d}{:02d}{:02d}'.format( date2.year, date2.month, date2.day ) )  ## 오늘+1의 날짜
    days.append( '{:04d}{:02d}{:02d}'.format( date3.year, date3.month, date3.day ) )  ## 오늘+2의 날짜


    # 3일간 자료 가져오기
    for today in days:
        # if  today!='{:04d}{:02d}{:02d}'.format( date1.year, date1.month, date1.day ):  # 일단 오늘 자료만 가지고 온다.
        #     continue


        for theaterkey in dicTheaters.keys():

            # if  theaterkey != '0121':
            #     continue

            url = 'http://www.cgv.co.kr/common/showtimes/iframeTheater.aspx?areacode='+dicTheaters[theaterkey][0]+'&theatercode='+theaterkey+'&date='+days[0]+''
            # print(url)
            r = http.request( 'GET', url )

            data = r.data.decode( 'utf-8' )
            # print(data)

            dicTicketMovies = {} #

            soup = BeautifulSoup( data, 'html.parser' )

            tags1 = soup.select( "div.sect-showtimes > ul > li > div.col-times" )
            for tag1 in tags1:
                # print(tag1)

                moviecode = ''
                moviename = ''
                movieplaying = ''
                moviegenre = ''
                movieruntime = ''
                moviereleasedate= ''

                tags2 = tag1.select("div.info-movie > a")
                for tag2 in tags2:
                    href = tag2['href']
                    hrefs = href.split( '=' )

                    moviecode = hrefs[1]
                    # print(hrefs[1])

                tags2 = tag1.select("div.info-movie > a > strong") # tags2[0]
                # print([tag2.text.strip() for tag2 in tags2])
                moviename =tag2.text.strip()

                tags2 = tag1.select( "div.info-movie > span.ico-grade" )
                # print( [ tag2.text.strip() for tag2 in tags2] )
                for tag2 in tags2:
                    moviegrade = tag2.text.strip()

                tags2 = tag1.select( "div.info-movie > span.round > em" )
                # print( [tag2.text.strip() for tag2 in tags2] )
                for tag2 in tags2:
                    movieplaying = tag2.text.strip()

                tags2 = tag1.select( "div.info-movie > i" )
                j = 0
                for tag2 in tags2:
                    j = j + 1
                    if j == 1 : moviegenre = tag2.text.strip().replace( '\xa0', ' ' ).replace( "\r\n", "" )
                    if j == 2 : movieruntime = tag2.text.strip().replace( '\xa0', ' ' ).replace( "\r\n", "" )
                    if j == 3 :
                        moviereleasedate = tag2.text.strip().replace( '\xa0', ' ' ).replace( "\r\n", "" )
                        moviereleasedate = moviereleasedate[0:4] + moviereleasedate[5:7] + moviereleasedate[8:10]
                    # print( str( j ) + ' ] ' + tag2.text.strip().replace( '\xa0', ' ' ).replace( "\r\n", "" ) )

                dicTicketRooms = {} #

                j=0
                tags2 = tag1.select( "div.type-hall" )
                for tag2 in tags2:
                    j=j+1
                    tags3 = tag2.select( "div.info-hall > ul > li" )

                    k = 0
                    for tag3 in tags3:
                        k = k + 1
                        if k == 1:
                            filmtype   = tag3.text.strip().replace("\r\n", "")
                        if k == 2:
                            roomfloor    = tag3.text.strip().replace("\r\n", "")
                        if k == 3:
                            totalseat = tag3.text.strip().replace("\r\n", "").split( )
                            totalseat = totalseat[1]
                        # print( str(j) + ' / ' + tag3.text.strip().replace("\r\n", "") )

                    dicTicketTiomes = {}  #

                    k = 0
                    tags3 = tag2.select( "div.info-timetable > ul > li" )
                    for tag3 in tags3:
                        k = k + 1
                        tags4 = tag3.select( "a" )

                        playtime = ''
                        playinfo = ''
                        playetc = ''

                        if  len(tags4) > 0: # print( '일반' )

                            tags4 = tag3.select( "a > em" )
                            for tag4 in tags4:
                                playtime = tag4.text
                                # print( tag4.text )

                            tags4 = tag3.select( "a > span" )
                            for tag4 in tags4:
                                tags5 = tag4.select("span")
                                for tag5 in tags5:
                                    tag5.extract()
                            for tag4 in tags4:
                                playinfo = tag4.text
                                # print( tag4.text )

                                for v in tag4.attrs.values():
                                    if v[0] == 'early':
                                        playetc = '조조'
                                        # print( "조조" )
                                    if v[0] == 'midnight':
                                        playetc = '심야'
                                        # print( "심야" )

                        else: # print( '마감' )

                            tags4 = tag3.select( "em" )
                            for tag4 in tags4:
                                playtime = tag4.text
                                # print( tag4.text )

                            tags4 = tag3.select( "span" )
                            for tag4 in tags4:
                                playinfo = tag4.text
                                # print( tag4.text )

                        dicTicketTiomes[k] = [playtime, playinfo, playetc]

                    dicTicketRooms[j] = [filmtype, roomfloor, totalseat, dicTicketTiomes]

                print(dicTicketRooms)
                dicTicketMovies[moviecode] = [today, moviename, moviegrade, movieplaying, moviegenre, movieruntime, moviereleasedate, dicTicketRooms]

            dicTicketingData[theaterkey] = dicTicketMovies
#
# 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/)의 프래임에서 상영정보를 가지고 온다. (dicTicketMovies)
#


#########################################################################################################################################
# CGV 클로링 정보 올리기..
#
def crawl_cgv_upload():

    fields = { "movies": str( dicMovies )
             , "regions": str( dicRegions )
             , "theater": str( dicTheaters )
             , "ticketingdata": str( dicTicketingData )
             }
    url = 'http://www.mtns7.co.kr/totalscore/cgv_upload.php'

    r = http.request( 'POST', url, fields )

    data = r.data.decode( 'utf-8' )
    print( data )
#
# CGV 클로링 정보 올리기..
#


#########################################################################################################################################
#########################################################################################################################################
#########################################################################################################################################
#########################################################################################################################################
if  __name__ == '__main__':

    ####crawl_cgv_moviechart() # 영화/무비차트(http://www.cgv.co.kr/movies/?ft=0) 애서 영화정보를 가지고온다. (dicMovies)

    ##crawl_cgv_moviefinder() # 영화/무비파인더(http://www.cgv.co.kr/movies/finder.aspx) 에서 영화데이터를 가지고 온다. (dicMovies)

    ##crawl_cgv_theaters() # 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/) 극장정보를 가지고 온다. (dicTheaters)

    crawl_cgv_showtimes() # 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/)의 프래임에서 상영정보를 가지고 온다. (dicTicketMovies)

    crawl_cgv_upload()
#########################################################################################################################################
#########################################################################################################################################
#########################################################################################################################################
#########################################################################################################################################
