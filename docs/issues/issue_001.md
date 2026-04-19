# Issue: is_id_verified not changing state from python
---

## Severity:
- [x] Critical
- [ ] High
- [ ] Medium
- [ ] Low

## Description:
When users image is taken through image process for they ID no matter what the database will not update the is_id_verified

## Steps to replicate
Just go to the profile page and validate ID give it a minute or so for the AI to determine the process and you will see even in the case of failure it will not change

## Expected behaviour
1. Use a test fake ID I expect the users `is_id_verified` to be set to 3 which is the state of failure.
2. When successfuly `is_id_verified` to be in a state of 1 which if for success.

## Actual behaviour
1. Freezes after the upload and loading the image


## What caused the error
1. EasyOCR was what i used it loads a full deeplearning model which blocks async event loops entirely while running inference
2. Its being called synchronously inside an async function freezing everything
3. The task result gets lost in background task completely after the response is already sent with no await anchoring it



## The Fix Descision
Replaced EasyOCR with a lightweight alternative being Tessaract via `pytesseract` it is called in CPU onlt has no model loading overload and runs in ms on a clean ID photo, and we run it on thread pools so it doesnt block the loop