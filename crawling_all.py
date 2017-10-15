'''

CGV, LOTTE, MEGA BOX

http://www.cgv.co.kr/
http://www.lottecinema.co.kr/
http://www.megabox.co.kr/

'''
import crawling_cgv as cgv
import crawling_mega as mega
import crawling_lotte as lotte

if  __name__ == '__main__':

    # CGV
    cgv.crawl_cgv_moviefinder( True )  # 영화/무비파인더(http://www.cgv.co.kr/movies/finder.aspx) 에서 영화데이터를 가지고 온다. (dicMovies) - 화면 서비스가 정지 될 수 있어서.. 그 경우 위의 함수를 호출한다.

    cgv.crawl_cgv_theaters( True )  # 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/) 극장정보를 가지고 온다. (dicTheaters)

    cgv.crawl_cgv_showtimes( True )  # 예매/상영시간표(http://www.cgv.co.kr/reserve/show-times/)의 프래임에서 상영정보를 가지고 온다. (dicTicketMovies)

    cgv.crawl_cgv_upload()

    # LOTTE

    lotte.crawl_lotte_boxoffice( True )  # 영화 / 박스 오피스(http://www.lottecinema.co.kr/LCHS/Contents/Movie/Movie-List.aspx) 에서 영화데이터를 가지고 온다. (dicMovieData)

    lotte.crawl_lotte_cinema( True )  # 영화관 (http://www.lottecinema.co.kr/LCHS/Contents/Cinema) 에서 극장데이터를 가지고 온다. (dicCinemas)

    lotte.crawl_lotte_ticketingdata( True )  # 영화관 (http://www.lottecinema.co.kr/LCWS/Ticketing/TicketingData.aspx) 에서 극장데이터를 가지고 온다. (dicTicketingData)

    # MEGA BOX

    mega.crawl_mega_movie(True) # 영화(http://www.megabox.co.kr/?menuId=movie) 에서 영화데이터를 가지고 온다. (dicMovies)

    mega.crawl_mega_cinema(True) # 영화관(http://www.megabox.co.kr/?menuId=theater)에서 지역데이터,영화관데이터를 가지고 온다. (dicRegions,dicCinemas)

    mega.crawl_mega_schedule(True) # 영화관(http://www.megabox.co.kr/?menuId=theater)에서 영화관에 스케줄데이터를 가지고 온다. (dicRegions,dicCinemas)

    mega.crawl_mega_upload()

# if  __name__ == '__main__':

