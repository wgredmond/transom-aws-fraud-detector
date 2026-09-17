# Magento 2 / AWS Fraud Detector Integration

## About the Project

This was a practical integration project: connect an e-commerce transaction system to an external fraud-detection service and make the result useful inside the existing order workflow.

The interesting part for me wasn't simply making an AWS API call. It was figuring out where the fraud decision belonged in the Magento transaction lifecycle, what information should be sent to the service, how the result should affect an order, and how to keep those pieces reasonably separated.

There are things I would implement differently today, particularly around logging, testing, AWS credential management and separation of services. I've left the original implementation intact and documented those changes below rather than rewriting the project to make an older codebase look newer than it is.

## Technology

* PHP
* Magento 2
* AWS SDK for PHP
* AWS Fraud Detector
* Magento Event / Observer architecture
* Magento Dependency Injection
* Magento Admin configuration
* Magento extension attributes and resource models

## What It Does

The module listens to Magento order and payment events and builds a transaction event containing information such as:

* Order ID and amount
* Customer information
* Billing and shipping information
* IP address and user agent
* Payment-related information

The event is sent to AWS Fraud Detector using the AWS SDK.

AWS returns the model score and rule outcome. The module then uses that result to update the Magento order workflow.

Depending on the configured outcome, an order can:

* Continue as a legitimate order
* Be placed on hold for review
* Be flagged as suspected fraud
* Have the fraud score and outcome recorded with the order

The Magento administrator can configure AWS settings, detector information, outcomes and fraud thresholds.

## Architecture

The integration uses Magento's event/observer architecture rather than modifying the Magento checkout directly.

The basic flow is:

```text
Magento Checkout / Payment
          |
          v
Magento Order Event
          |
          v
OrderObserver
          |
          v
AWS Fraud Detector API
          |
          v
Fraud Score + Rule Outcome
          |
          v
OrderManager
          |
          +---- Legitimate ---> Continue processing
          |
          +---- Review -------> Hold / flag order
          |
          +---- Cancel -------> Flag / cancel order
          |
          v
Persist Fraud Result
```

The module is divided into a few primary responsibilities.

**Observer**

The order observer listens for Magento sales and payment events. When the appropriate payment event occurs, it passes the order and payment information into the fraud detection integration.

**AWS API Integration**

The API helper translates Magento order information into the event variables expected by AWS Fraud Detector and calls the AWS `GetEventPrediction` API.

It then reads the model score and rule outcome returned by AWS.

**Order Management**

The `OrderManager` translates the AWS outcome into Magento behavior.

This keeps the fraud decision separate from the Magento order action and allows automatic order status changes to be enabled or disabled through configuration.

**Configuration**

`ConfigSettings` provides a single place for retrieving the Magento configuration used by the integration, including:

* AWS region
* AWS credentials
* Detector ID
* Event type
* Model score name
* Review and cancellation thresholds
* Fraud outcomes
* Automatic order-status behavior

Sensitive AWS configuration is stored using Magento's encrypted configuration support.

**Persistence**

Fraud scores and outcomes are persisted so the AWS decision remains associated with the Magento order and can be referenced during later order processing.

## Production Readiness

This repository represents the working integration and the architecture I was exploring. Before putting this version into a production environment today, I would make several changes.

### TODO

* [ ] Remove the development-level order and payment logging and replace it with configurable, structured logging.
* [ ] Make sure customer and payment information is never unnecessarily written to application logs.
* [ ] Move debug behavior into Magento configuration/environment settings rather than enabling it in code.
* [ ] Replace direct access to PHP request globals with Magento request abstractions.
* [ ] Improve handling of missing IP addresses rather than substituting a localhost address.
* [ ] Move AWS client construction into a dedicated injectable service.
* [ ] Prefer AWS IAM roles / the AWS credential provider chain over application-managed access keys where the hosting environment supports it.
* [ ] Separate Magento-to-AWS event mapping from the API client to make both easier to test and maintain.
* [ ] Add PHPUnit tests for legitimate, review, cancellation, missing-data and AWS failure scenarios.
* [ ] Add integration tests around Magento order-state transitions.
* [ ] Add stronger validation of AWS configuration before enabling fraud processing.
* [ ] Review failure and retry behavior so temporary AWS outages do not interfere with checkout.
* [ ] Consider moving fraud evaluation to a queue/background process where the business requirements allow it.
* [ ] Add static analysis, linting and automated tests to the CI pipeline.
* [ ] Review the module against the current Magento and AWS SDK versions before deployment.
