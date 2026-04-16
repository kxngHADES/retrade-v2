# ID verification logic

## Values meaning

| number | meaning |
| --- | --- |
| 0 | Unverified |
| 1 | Verified |
| 2 | Pending verificatin |
| 3 | Failed verification |

## Rules

* changing to 1 or 3 can only be done by the python logic
     * Reason:
       *   This makes it so the state of the ID verification is completed by the OCR python logic which runs a Luhn algorithm to determine if it is a valid ID or not

* Future Improvements
   * Using an API to verifiy that the user is who they claim to be via the Department of Home affairs.


## RBAC

* When `is_id-verified` completed then increase the RBAC from 0 to 1 this will allow the user to be able to start selling on the platform and hold the liable for any miss conduct done