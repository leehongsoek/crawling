'''
CGV 에서 자료가져오기


http://img.cgv.co.kr/R2014/js/app.utils.js 에 유의


호출부
app.movie().getList({ listType: '1', orderType: '1', filterType: '0' }, setMovieListBuild);

구현부
                getList: function (data, callback) {
                var url = '/common/ajax/movies.aspx/GetMovieMoreList';
                app.ajax().get({ dataType: 'json', url: url, data: data, contentType: "application/json; charset=utf-8", successHandler: callback });

get: function (options) {
                if (!options.url) $.error('not defined url.');
                var url = getUrl(options.url),
                    defaults = {
                        type: "GET",
                        url: url,
                        data: options.data
                    },
                    config = $.extend(defaults, options);

                config.url = url;

                //app.log('[ajax] get url : ' + url);
                //app.log(options.data);

                $.ajax(config).done(function (result) {
                    //app.log('[ajax] get result : ', result);
                    //app.log(result);
                    var r = result;
                    if (config.dataType === 'json' && r.d) {
                        r = $.parseJSON(result.d);
                    }

                    options.loading && options.loading.hide();
                    if (!result.hasErrors) {
                        options.successHandler && options.successHandler(r);
                    } else {
                        app.errorHandler(r);
                    }
                }).fail(function (jqxhr, textStatus, error) {
                    options.loading && options.loading.hide();
                    app.failHandler(error);
                });
            },

'''


import datetime, time
import urllib3  # pip install urllib3
from bs4 import BeautifulSoup
import json
from jsonpath_rw import jsonpath, parse  # pip install jsonpath-rw      https://pypi.python.org/pypi/jsonpath-rw
from multiprocessing import Queue # python Setup.py build # exe 파일 생성을 위해 꼭 필요
from selenium import webdriver # pip install selenium


dicRegions = {}  # 지역코드 정보
dicTheaters = {} # 극장코드 정보
dicMovies = {}   # 영화 코드 정보
dicTicketingData = {}  # 티켓팅 정보

http = urllib3.PoolManager()




def func_test():

    '''

    url = 'http://www.cgv.co.kr/common/ajax/movies.aspx/GetMovieMoreList?listType=1&orderType=1&filterType=0&_=' # &_=1505471014753
    r = http.request( 'GET', url )
    data = r.data.decode( 'utf-8' )

    print(data)
    '''

    # ChromeDriver - WebDriver for Chrome (https://sites.google.com/a/chromium.org/chromedriver/downloads) 에서 [Latest Release: ChromeDriver x.xxx] 다운로드 (widows 용으로)
    driver = webdriver.Chrome( 'C:/chromedriver_win32/chromedriver.exe' ) # 압축을 푼 실행파일을 해당경로에 푼다.....

    driver.implicitly_wait( 3 )

    driver.get( 'http://www.cgv.co.kr/movies/' )
    # 로그인 버튼을 눌러주자.
    driver.implicitly_wait( 3 )
    driver.find_element_by_xpath( '//*[@class="btn-more-fontbold"]' ).click()

    driver.implicitly_wait( 30 )

    '''
    binary = 'C:/chromedriver_win32/chromedriver.exe'
    browser = webdriver.Chrome( binary )
    browser.get( 'http://naver.com' )
    browser.quit()
    '''


#
#
#


#------------------------------------------------------------------------------------------------------------------------------------------------------
# 영화 / 무비파인더(http://www.cgv.co.kr/movies/finder.aspx) 에서 영화데이터를 가지고 온다. (dicMovies)
#
def func_cgv_moviefinder():
    # 1 ~ 페이지 에서 부터 영화정보 (코드+이름+개봉일) 를 가지고 온다...
    for i in range(1,10): # 282

        # if i != 1:
        #     continue

        url = 'http://www.cgv.co.kr/movies/finder.aspx?s=true&sdate=1960&edate=2020&page='+str(i) # 무비파인더 에서 영화 리스트

        r = http.request( 'GET', url )

        data = r.data.decode( 'utf-8' )
        # print(data)

        soup = BeautifulSoup( data, 'html.parser' )

        tags1 = soup.select( "div.sect-search-chart > ol" )
        for tag1 in tags1:
            # print( tag1 )

            tags2 = tag1.select( "li" )
            for tag2 in tags2:
                # print( tag2 )

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

                        # print( ' +{}+ '.format( movieopen ) )

                dicMovies[moviecode] = [moviename, releasedate]  # 영화데이터 정보
#
#
#



#------------------------------------------------------------------------------------------------------------------------------------------------------
# 예매/상영시간표 에서 극장정보를 가지고 온다. (dicTheaters)
#
def func_cgv_theaters():

    url = 'http://www.cgv.co.kr/reserve/show-times/'
    r = http.request( 'GET', url )

    data = r.data.decode( 'utf-8' )
    # print(data)

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

                    dicTheaters[theatercode] = [regioncode, dicRegions[regioncode], theatername]  # 극장코드 정보 추가 (지역코드+지역명+극장명)
#
#
#


def func_cgv_showtimes():
    days = []

    date1 = datetime.date.today()  ## 오늘자 날짜객체
    date2 = date1 + datetime.timedelta( days=1 )
    date3 = date2 + datetime.timedelta( days=1 )

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








def func_cgv_upload():

    fields = { "movies": str( dicMovies )
             , "regions": str( dicRegions )
             , "theater": str( dicTheaters )
             , "ticketingdata": str( dicTicketingData )
             }
    url = 'http://www.mtns7.co.kr/totalscore/cgv_upload.php'

    r = http.request( 'POST', url, fields )

    data = r.data.decode( 'utf-8' )
    print( data )



if  __name__ == '__main__':

    func_test()

    func_cgv_moviefinder()

    # for dicMovie in dicMovies:
    #     print( '{} // {}'.format( dicMovie, dicMovies[dicMovie] ) )

    func_cgv_theaters()


    # for dicRegion in dicRegions:
    #     print( '{} // {}'.format( dicRegion, dicRegions[dicRegion] ) )
    #
    # for dicTheater in dicTheaters:
    #     print( '{} // {}'.format( dicTheater, dicTheaters[dicTheater] ) )

    # func_cgv_cinema()
    # # for dicCinema in dicCinemas:
    # #     print( dicCinema + ' // ' + str( dicCinemas[dicCinema] ) )
    #
    # func_cgv_ticketingdata()
    # # for dicMovie in dicMovies:
    # #     print( dicMovie + ' // ' + str( dicMovies[dicMovie] ) )
    # # for dicTicketingDatum in dicTicketingData:
    # #     print( dicTicketingDatum + ' // ' + str( dicTicketingData[dicTicketingDatum] ) )

    func_cgv_showtimes()

    for dicTicketingDatum in dicTicketingData:
        print( dicTicketingDatum + ' // ' + str( dicTicketingData[dicTicketingDatum] ) )

    func_cgv_upload()
