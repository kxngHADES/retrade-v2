from pathlib import Path
from pydantic import EmailStr
from app.core.config import settings
import smtplib
from email.message import EmailMessage

BASE_DIR = Path(__file__).resolve().parent.parent


async def send_sms(phone: str, message: str):
	msg = EmailMessage()
	msg.set_content(message)

	msg['From'] = settings.SMTP_USER
	msg['To'] = f"{phone}@{settings.CARRIER_GATEWAY}"
	msg['Subject'] = f'OTP to {phone}'

	try:
		server = smtplib.SMTP(settings.SMTP_HOST, settings.SMTP_PORT)
		server.starttls()

		server.login(settings.SMTP_USER, settings.SMTP_PASS.get_secret_value())
		
		server.send_message(msg)
		server.quit()
		return True
	except Exception:
		return False

async def send_email(to_email: EmailStr, template_name: str, subject: str,**context):
	msg = EmailMessage()
	msg['Subject'] = subject
	msg['From'] = settings.SMTP_USER
	msg['To'] = to_email

	template_path = BASE_DIR / "templates" / "email" / f"{template_name}.html"

	if not template_path.exists():
		raise FileNotFoundError(f"Email templaye `{template_name}.html` NOT FOUND")

	raw_html = template_path.read_text(encoding="utf-8")

	from string import Template
	html_content = Template(raw_html).safe_substitute(**context)

	msg.set_content("Please enable HTML to view this message")
	msg.add_alternative(html_content, subtype='html')

	server = smtplib.SMTP(settings.SMTP_HOST, settings.SMTP_PORT)
	server.starttls()
	server.login(settings.SMTP_USER, settings.SMTP_PASS.get_secret_value())

	server.send_message(msg)
	server.quit()

async def send_mass_email(to_emails: list[EmailStr], subject: str, html_body: str):
	"""Sends a mass email to multiple recipients individually to avoid disclosing list"""
	import datetime
	year = datetime.datetime.now().year
	
	for email in to_emails:
		try:
			await send_email(
				to_email=email,
				template_name="mass_broadcast",
				subject=subject,
				content=html_body,
				year=year
			)
		except Exception as e:
			print(f"Failed to send email to {email}: {e}")
			continue
