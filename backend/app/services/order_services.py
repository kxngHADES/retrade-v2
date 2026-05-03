from app.utils.email_helper import send_email
import logging

async def handle_escrow_notifications(buyer_email: str, seller_email: str, reference: str, pin: str):
    """
    Sends the PIN to the buyer and the Payment Reference to the seller when an order moves into escrow.
    """
    try:
        # 1. Send Reference to Seller
        await send_email(
            to_email=seller_email,
            template_name="seller_reference",
            subject="Payment Held in Escrow - Action Required",
            reference=reference
        )

        # 2. Send PIN to Buyer
        await send_email(
            to_email=buyer_email,
            template_name="buyer_pin",
            subject="Your Purchase Payment PIN",
            pin=pin
        )
        return True
    except Exception as e:
        logging.error(f"Error sending escrow emails: {str(e)}")
        return False
