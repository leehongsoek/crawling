import urllib.request
from bs4 import BeautifulSoup ## 모듈 설치 유의 (http://woongheelee.com/entry/PyCharm%EC%97%90%EC%84%9C-%EB%9D%BC%EC%9D%B4%EB%B8%8C%EB%9F%AC%EB%A6%AC-%EC%9E%84%ED%8F%AC%ED%8A%B8import%ED%95%98%EB%8A%94-%EB%B0%A9%EB%B2%95)


if __name__ == "__main__":

    req = urllib.request.Request("http://gall.dcinside.com/board/lists/?id=kimsohye");
    data = urllib.request.urlopen(req).read()

    bs = BeautifulSoup(data, 'html.parser')
    l = bs.find_all('a')

    idx = 0
    for s in l:
        try:
            print("%d : %s" % (idx, str(s)))
        except UnicodeEncodeError:
            print("Errror : %d" % (idx))
        finally:
            idx += 1
