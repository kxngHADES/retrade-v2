from app.core.config import settings
from app.utils.email_helper import send_email, send_sms
from pydantic import EmailStr


# Validate Email
async def validate_email(to_email: EmailStr, otp: int):
	template_name = "validate_email"
	subject = "ReTrade Verify Email Address"

	return await send_email(to_email=to_email, template_name=template_name, subject=subject, otp=otp)


# Validate Phone




# Validate ID
def validate_sa_id(id_number: str) -> bool:
	if not id_number.isdigit() or len(id_number) != 13:
		return False
		
	digits = [int(d) for d in id_number]
	checksum = digits.pop()
	
	for i in range(len(digits) - 1, -1, -1):
		if (len(digits) - i) % 2 == 1:
			doubled = digits[i] * 2
			digits[i] = doubled if doubled < 10 else (doubled // 10) + (doubled % 10)
		
		total = sum(digits)
		expected = (10 - (total % 10)) % 10
		return checksum == expected