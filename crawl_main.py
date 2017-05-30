import crawl_lotte
import pymysql # pip install pymysql

# crol_lotte.func_test()
crawl_lotte.func_lotte()


print('----------------------------------------')
for movkey in crawl_lotte.dicMovies.keys():
    print( movkey +':' + str( crawl_lotte.dicMovies[movkey] ) )
print('----------------------------------------')

