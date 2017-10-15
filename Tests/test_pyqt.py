# https://wikidocs.net/5222
import sys
from PyQt5.QtWidgets import *

def clicked_slot():
    print('clicked')

app = QApplication(sys.argv)
app.setApplicationDisplayName('나의 첫 pyqt5') # 추가

btn = QPushButton("한글, PyQt")
btn.clicked.connect(clicked_slot)
btn.show()

app.exec_()