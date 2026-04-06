import smtplib
from email.message import EmailMessage
from app.core.config import settings
from pydantic import EmailStr
import asyncio
from pathlib import Path

async def emailToSMS(phone:str, otp:int):
	msg = EmailMessage()
	msg.set_content(f"ReTrade. Nver share this One Time PIN with anyone. Use \n\nOTP: {otp} to verify your phone number {phone}")

	msg['From'] = settings.SMTP_USER
	msg['To'] = f"{phone}@{settings.CARRIER_GATEWAY}"
	msg['Subject'] = 'Phone number OTP'

	server = smtplib.SMTP(settings.SMTP_HOST, settings.SMTP_PORT)
	server.starttls()
	server.login(settings.SMTP_USER, settings.SMTP_PASS.get_secret_value())

	server.send_message(msg)
	server.quit()

async def send_email(to_email: EmailStr, subject: str ,template_name: str, **context):
	msg = EmailMessage()
	msg['Subject'] = subject
	msg['From'] = settings.SMTP_USER
	
	template_path = Path(__file__).parent.parent / "templates" / "email" / f"{template_name}.html"

	if not template_path.exists():
		raise FileNotFoundError(f"Email templaye `{template_name}.html` NOT FOUND")
	
	raw_html = template_path.read_text(encoding="utf-8")

	html_content = raw_html.format(**context)

	msg.set_content("Enable HTML to viw this message")
	msg.add_alternative(html_content, subtype='html')

	server = smtplib.SMTP(settings.SMTP_HOST, settings.SMTP_PORT)
	server.starttls()
	server.login(settings.SMTP_USER, settings.SMTP_PASS.get_secret_value())

	server.send_message(msg)
	server.quit()