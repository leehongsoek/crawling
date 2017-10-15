import smtplib
from email.mime.text import MIMEText

import datetime

smtp = smtplib.SMTP_SSL('smtp.mail.nate.com', 465)#( 'smtp.gmail.com', 465 )
smtp.login( 'lhs0806@nate.com', '???' )

msg = MIMEText( '본문 테스트 메시지' )
msg['Subject'] = '테스트 : '+ str(datetime.datetime.now())
msg['From'] = '이홍석 <lhs0806@nate.com>'
msg['To'] = 'lhs0806@nate.com'
smtp.sendmail( 'lhs0806@nate.com', 'lhs0806@nate.com', msg.as_string() )

smtp.quit()