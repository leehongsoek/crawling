
import logging # 파일로 남기기 위해 filename='./log/test.log' parameter로 추가한다.

logging.basicConfig(filename='./log/test.log', level=logging.DEBUG)
logging.debug("debug")
logging.info("info")
logging.warning("warning")
logging.error("error")
logging.critical("critical")
