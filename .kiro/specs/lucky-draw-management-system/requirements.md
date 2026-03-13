# Requirements Document

## Introduction

The Lucky Draw Management System is a sophisticated feature for a Laravel real estate application that enables property-based lucky draws with intelligent profit/loss protection. The system allows administrators to create and manage draws while providing users with an engaging way to win properties through paid participation. The system includes smart algorithms that protect business interests by selecting real vs dummy winners based on profitability calculations.

## Glossary

- **Lucky_Draw_System**: The complete lucky draw management feature
- **Admin_Panel**: Administrative interface for managing draws
- **User_Interface**: Public-facing interface for user participation
- **Smart_Algorithm**: Profit/loss protection algorithm for winner selection
- **Payment_Gateway**: UPI-based payment processing system
- **Credit_System**: Reward system for losing participants
- **Automation_Engine**: Cron job system for automated processing
- **Dummy_Winner**: Fake winner used for business protection
- **Real_Winner**: Actual user who wins the property
- **Entry_Fee**: Payment required to participate in a draw
- **Property_Value**: Market value of the property being offered
- **Total_Pool**: Sum of all entry fees collected
- **Profit_Margin**: Difference between total pool and property value

## Requirements

### Requirement 1: Admin Draw Management

**User Story:** As an administrator, I want to create and manage property-based lucky draws, so that I can offer engaging property acquisition opportunities while maintaining business control.

#### Acceptance Criteria

1. WHEN an administrator creates a new draw, THE Lucky_Draw_System SHALL store the draw with property details, entry fee, and scheduling information
2. WHEN an administrator sets draw dates, THE Lucky_Draw_System SHALL validate that start_date < end_date < draw_date
3. WHEN an administrator views the draw list, THE Lucky_Draw_System SHALL display all draws with status, participant count, and profit/loss indicators
4. WHEN an administrator edits an active draw, THE Lucky_Draw_System SHALL prevent modification of critical fields if participants exist
5. WHEN an administrator deletes a draw, THE Lucky_Draw_System SHALL prevent deletion if participants have joined
6. WHEN an administrator activates a draw, THE Lucky_Draw_System SHALL change status from 'upcoming' to 'active'
7. WHEN an administrator executes a draw, THE Lucky_Draw_System SHALL run the smart winner selection algorithm

### Requirement 2: Smart Winner Selection Algorithm

**User Story:** As a business owner, I want an intelligent winner selection system that protects against losses, so that the lucky draw feature remains profitable while providing real winners when possible.

#### Acceptance Criteria

1. WHEN profit margin is ≥20%, THE Smart_Algorithm SHALL always select a real winner
2. WHEN profit margin is 5-19%, THE Smart_Algorithm SHALL select a real winner with 80% probability
3. WHEN profit margin is -5% to 5%, THE Smart_Algorithm SHALL select a real winner with 50% probability
4. WHEN profit margin is ≤-5%, THE Smart_Algorithm SHALL select a dummy winner with 90% probability
5. WHEN selecting a real winner, THE Smart_Algorithm SHALL use weighted selection favoring early participants
6. WHEN selecting a dummy winner, THE Smart_Algorithm SHALL choose randomly from active dummy winner database
7. WHEN calculating profit margin, THE Smart_Algorithm SHALL use formula: (total_pool - property_value) / property_value * 100

### Requirement 3: User Participation System

**User Story:** As a user, I want to participate in lucky draws by paying entry fees, so that I can have a chance to win properties at a fraction of their market value.

#### Acceptance Criteria

1. WHEN a user views active draws, THE User_Interface SHALL display draw details, property information, and participation statistics
2. WHEN a user joins a draw, THE Lucky_Draw_System SHALL create a participation record with pending payment status
3. WHEN a user makes payment, THE Payment_Gateway SHALL generate UPI QR codes with correct payment details
4. WHEN a user submits payment proof, THE Lucky_Draw_System SHALL update payment status to 'paid'
5. WHEN a user is already participating, THE Lucky_Draw_System SHALL prevent duplicate participation
6. WHEN a draw ends, THE Lucky_Draw_System SHALL only include paid participants in winner selection
7. WHEN a user wins, THE Lucky_Draw_System SHALL mark their participation as winner and update account statistics

### Requirement 4: Payment Processing System

**User Story:** As a user, I want to make secure payments for draw participation, so that I can complete my entry and be eligible for winner selection.

#### Acceptance Criteria

1. WHEN a user initiates payment, THE Payment_Gateway SHALL generate UPI payment strings with correct merchant details
2. WHEN generating QR codes, THE Payment_Gateway SHALL include draw name, amount, and merchant information
3. WHEN a user uploads payment proof, THE Lucky_Draw_System SHALL store the screenshot and UTR number
4. WHEN payment is verified, THE Lucky_Draw_System SHALL update total pool amount for the draw
5. WHEN payment fails or is invalid, THE Lucky_Draw_System SHALL maintain pending status
6. WHEN a draw is cancelled, THE Lucky_Draw_System SHALL support payment refund processing

### Requirement 5: Credit System for Losing Participants

**User Story:** As a losing participant, I want to receive credits for future property purchases, so that my participation has value even when I don't win.

#### Acceptance Criteria

1. WHEN a draw is completed with profit, THE Credit_System SHALL award 100% of entry fee as credits to losing participants
2. WHEN a draw is completed without profit, THE Credit_System SHALL award 80% of entry fee as credits to losing participants
3. WHEN credits are awarded, THE Credit_System SHALL update the participant's account balance
4. WHEN credits are awarded, THE Credit_System SHALL record the credit amount in the participation record
5. WHEN a user views their account, THE Credit_System SHALL display available credits balance
6. WHEN credits are used for property purchases, THE Credit_System SHALL deduct from available balance

### Requirement 6: Automated Draw Processing

**User Story:** As a system administrator, I want automated processing of draw lifecycle events, so that draws are activated and executed without manual intervention.

#### Acceptance Criteria

1. WHEN the current time reaches a draw's start_date, THE Automation_Engine SHALL change status from 'upcoming' to 'active'
2. WHEN the current time reaches a draw's draw_date, THE Automation_Engine SHALL execute the draw and select winners
3. WHEN automation runs, THE Automation_Engine SHALL process multiple draws in a single execution
4. WHEN automation completes, THE Automation_Engine SHALL log execution results and statistics
5. WHEN automation encounters errors, THE Automation_Engine SHALL log errors and continue processing other draws
6. WHEN automation is scheduled, THE Automation_Engine SHALL run via Laravel's command scheduler

### Requirement 7: Winner Notification System

**User Story:** As a winner, I want to be notified immediately when I win a draw, so that I can take necessary steps to claim my property.

#### Acceptance Criteria

1. WHEN a real winner is selected, THE Lucky_Draw_System SHALL send congratulatory email with draw details
2. WHEN sending winner emails, THE Lucky_Draw_System SHALL include property information, next steps, and contact details
3. WHEN a dummy winner is selected, THE Lucky_Draw_System SHALL log the selection without sending emails
4. WHEN notifications are sent, THE Lucky_Draw_System SHALL use configured email templates and sender information
5. WHEN email sending fails, THE Lucky_Draw_System SHALL log the failure but continue draw processing

### Requirement 8: Public Winner Display

**User Story:** As a visitor, I want to see recent winners and their testimonials, so that I can trust the legitimacy of the lucky draw system.

#### Acceptance Criteria

1. WHEN displaying recent winners, THE User_Interface SHALL show both real and dummy winners
2. WHEN showing winner information, THE User_Interface SHALL display name, draw details, and property value
3. WHEN displaying dummy winners, THE User_Interface SHALL show pre-configured testimonials and avatars
4. WHEN displaying real winners, THE User_Interface SHALL show actual user information (with privacy protection)
5. WHEN no avatar is available, THE User_Interface SHALL generate placeholder avatars based on names

### Requirement 9: User Dashboard and Statistics

**User Story:** As a user, I want to view my participation history and statistics, so that I can track my draws and manage my credits.

#### Acceptance Criteria

1. WHEN a user accesses their dashboard, THE User_Interface SHALL display active participations with draw status
2. WHEN showing participation history, THE User_Interface SHALL include completed draws with win/loss status
3. WHEN displaying user statistics, THE User_Interface SHALL show total draws joined, won, and available credits
4. WHEN a user has won draws, THE User_Interface SHALL highlight winning participations
5. WHEN showing credits, THE User_Interface SHALL display current balance and credit history

### Requirement 10: Data Persistence and Integrity

**User Story:** As a system administrator, I want reliable data storage with proper relationships, so that all draw information is accurately maintained and retrievable.

#### Acceptance Criteria

1. WHEN storing draw data, THE Lucky_Draw_System SHALL maintain referential integrity between draws, properties, and participants
2. WHEN updating participant status, THE Lucky_Draw_System SHALL use database transactions to ensure consistency
3. WHEN calculating totals, THE Lucky_Draw_System SHALL aggregate from participant records rather than storing duplicates
4. WHEN deleting records, THE Lucky_Draw_System SHALL respect foreign key constraints and prevent orphaned data
5. WHEN storing financial data, THE Lucky_Draw_System SHALL use appropriate decimal precision for currency values
6. WHEN logging activities, THE Lucky_Draw_System SHALL maintain audit trails for all critical operations