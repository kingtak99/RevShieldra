# 🤖 Chatbot Flow System - Comprehensive Documentation

## 📋 Overview

The Chatbot Flow System is a hierarchical, multi-language support platform that routes users through intelligent decision trees to find answers or escalate to support tickets.

### Key Features
- **5 Main Flows** with branching structures
- **Bilingual Support** (Arabic SA, English GB)
- **Smart Routing** with back navigation and visual separators
- **Ticket Context Tracking** - support team knows user's journey
- **Session-Based Flow State** - maintains user context throughout conversation
- **Database Logging** - all interactions logged with metadata

---

## 🌳 Flow Architecture

### The 5 Main Flows

#### 1. 💰 Pricing & Subscriptions
**Purpose:** Answer questions about pricing, plans, and billing
- **Branches:**
  - `plans` → Pricing information + [Back, Support]
  - `payment` → Payment questions + [Back, Support]
  - `upgrade` → Upgrade information + [Back]
- **Support Category:** General (no specific category set)

#### 2. 🚀 Platform Overview & Workflow
**Purpose:** Explain how the platform works
- **Branches:**
  - `about` → Platform overview + [Back]
  - `smart_routing` → Smart routing explanation + [Back]
- **Support Category:** None (FAQ-only)

#### 3. 🛠️ Branches & QR Setup
**Purpose:** Technical setup and configuration
- **Branches:**
  - `create_branch` → How to create branches + [Back, Support]
  - `qr_generation` → QR code generation + [Back]
  - `branch_limits` → Branch limits information + [Back, Support]
- **Support Category:** Technical

#### 4. 🔐 Data Security & Privacy
**Purpose:** Security and data handling questions
- **Branches:**
  - `data_protection` → Data protection details + [Back]
  - `data_retention` → Data retention policy + [Back, Support]
- **Support Category:** None (information-only)

#### 5. 🚨 Technical Support & Troubleshooting
**Purpose:** PRIMARY TICKET ENTRY POINT - Only flow with mandatory ticket support
- **Branches:**
  - `billing_issues` (category: `billing`) → Billing problems + [Back, Support]
  - `technical_issues` (category: `technical`) → Technical issues + [Back, Support]
- **Support Category:** Billing/Technical

**IMPORTANT:** This is the only flow with support buttons in both branches. All tickets should originate from this flow.

---

## 🔌 API Endpoints

### Endpoint: `/chatbot/process` (POST)

All actions go through a single endpoint. Required parameters in every request:
- `session_id` (string): Unique session identifier
- `action` (string): One of: `flow`, `navigate`, `back`, `ticket`, `chat`
- `language` (string): `ar` (Arabic) or `en` (English)

### Action: `flow`
**Purpose:** Get the root menu with all 5 flows

```json
{
  "session_id": "session_12345",
  "action": "flow",
  "language": "ar"
}
```

**Response Example (Arabic):**
```json
{
  "reply": "Select one of the following options",
  "type": "menu",
  "options": [
    {
      "key": "pricing",
      "label": "💰 Pricing & Subscriptions",
      "icon": "💰"
    },
    {
      "key": "platform",
      "label": "🚀 Platform Overview & Workflow",
      "icon": "🚀"
    },
    ...
  ]
}
```

### Action: `navigate`
**Purpose:** Navigate to a flow's branches or get full branch data

#### Variant 1: List branches for a flow
```json
{
  "session_id": "session_12345",
  "action": "navigate",
  "language": "ar",
  "flow_key": "troubleshooting"
}
```

**Response:** List of branches with back button
```json
{
  "reply": "Select your issue type",
  "type": "menu",
  "options": [
    {
      "key": "billing_issues",
      "label": "💳 Billing Issues",
      "branch_key": "billing_issues"
    },
    {
      "key": "technical_issues",
      "label": "🔧 Technical Issues",
      "branch_key": "technical_issues"
    }
  ],
  "sub_options": [
    {
      "action": "back",
      "label": "⬅️ Back to Main Menu"
    }
  ]
}
```

#### Variant 2: Get full branch data
```json
{
  "session_id": "session_12345",
  "action": "navigate",
  "language": "ar",
  "flow_key": "troubleshooting",
  "branch_key": "billing_issues"
}
```

**Response:** Full branch with response and action buttons
```json
{
  "reply": "Detailed response about billing issues...",
  "type": "branch",
  "category": "billing",
  "sub_options": [
    {
      "action": "back",
      "label": "⬅️ Back to Troubleshooting"
    },
    {
      "action": "support",
      "label": "🎫 Request Support Ticket"
    }
  ]
}
```

### Action: `back`
**Purpose:** Return to previous menu level

```json
{
  "session_id": "session_12345",
  "action": "back",
  "language": "ar"
}
```

**Response:** Previous menu with navigation message
```json
{
  "reply": "⬅️ Returned to previous menu",
  "type": "menu",
  "options": [...]
}
```

### Action: `ticket`
**Purpose:** Submit a support ticket with flow context

```json
{
  "session_id": "session_12345",
  "action": "ticket",
  "language": "ar",
  "name": "Ahmed Hassan",
  "email": "ahmed@example.com",
  "message": "I'm having trouble with my billing",
  "category": "billing"
}
```

**Response:** Confirmation message
```json
{
  "reply": "✅ Thank you for your message! Our support team will reply shortly.",
  "type": "confirmation",
  "flow": "end"
}
```

### Action: `chat`
**Purpose:** Free-form chat messages (fallback)

```json
{
  "session_id": "session_12345",
  "action": "chat",
  "language": "ar",
  "message": "What's your pricing?"
}
```

---

## 💾 Database Schema

### chatbot_logs Table

```sql
CREATE TABLE chatbot_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NULLABLE FOREIGN KEY,
  user_email VARCHAR(255) NULLABLE,
  session_id VARCHAR(255),
  sender ENUM('user', 'bot', 'system'),
  message TEXT,
  language VARCHAR(2) DEFAULT 'ar',
  log_type ENUM('chat', 'ticket_request') DEFAULT 'chat',
  metadata JSON NULLABLE,  -- NEW: Stores flow context
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
```

### Metadata JSON Structure

When a ticket is submitted, `metadata` contains:
```json
{
  "flow": "troubleshooting",
  "branch": "billing_issues",
  "category": "billing",
  "flow_path": ["troubleshooting", "billing_issues"]
}
```

---

## 🎯 Frontend Integration

### Using the chatbot-widget.blade.php

The widget is pre-built with all the necessary functionality:

```blade
@include('partials.chatbot-widget')
```

**Widget includes:**
- Language selection (Arabic/English)
- Full flow navigation
- Back buttons with visual separators (dashed borders)
- Support ticket form (red-themed)
- Chat message display with auto-scroll
- Restart button

### Key JavaScript Functions

- `setChatLanguage(lang)` - Initialize with language selection
- `showRootMenu()` - Display all 5 flows
- `navigateToFlow(flowKey)` - Navigate to specific flow
- `selectBranch(flowKey, branchKey)` - Select a branch
- `showSubOptions(subOptions)` - Display back/support buttons
- `openSupportTicket(category)` - Open ticket form with category
- `submitSupportTicket()` - Send ticket with all metadata

---

## 🧪 Testing Checklist

### Manual Testing
- [ ] Load chatbot in browser
- [ ] Select Arabic language
- [ ] Navigate through each of the 5 flows
- [ ] Test back button at every level
- [ ] Submit a support ticket
- [ ] Verify category is displayed in email
- [ ] Repeat all tests with English language
- [ ] Test free-form chat as fallback
- [ ] Test on mobile device (responsive design)

### Database Testing
```sql
-- Check if tickets are being logged with metadata
SELECT id, session_id, message, metadata FROM chatbot_logs 
WHERE log_type = 'ticket_request' 
ORDER BY created_at DESC LIMIT 10;

-- Check flow navigation logs
SELECT id, session_id, message, log_type FROM chatbot_logs 
WHERE session_id = 'test_session' 
ORDER BY created_at;
```

### Browser Console Testing
Use the test file at `/public/test-chatbot-flows.js`:
1. Open browser console
2. Copy and paste the test code
3. Verify each endpoint returns correct JSON

---

## 📧 Email Integration

The `SupportTicketMail` class receives ticket data with:
- `category`: The issue category (billing/technical/general)
- `flow_context`: The user's navigation path
- `branch`: Where they submitted from

To display category in email template:
```blade
Category: {{ $ticketData['category'] ?? 'General' }}
Flow: {{ implode(' → ', $ticketData['flow_context'] ?? []) }}
```

---

## 🔄 Flow Context Tracking

### Session Storage
```
Key: chatbot_flow_{$sessionId}
Value: {
  "flow": "troubleshooting",
  "branch": "billing_issues",
  "category": "billing"
}
```

### When it's set:
- When user navigates to a branch via `navigate` action

### When it's used:
- When user clicks "Submit Ticket"
- Metadata is saved to `chatbot_logs.metadata` JSON field

### Why it matters:
- Support team knows exactly where user came from
- Category helps route to correct department
- Flow path provides context for support response

---

## 🚀 Deployment Checklist

- [ ] Run `php artisan migrate` to add metadata column
- [ ] Verify `ChatbotFlowService.php` is in `app/Services/`
- [ ] Verify `ChatbotController` has all new actions
- [ ] Update `SupportTicketMail` template if needed
- [ ] Test all flows in staging environment
- [ ] Verify email template displays category
- [ ] Check responsive design on mobile
- [ ] Review browser console for errors
- [ ] Verify database logs contain metadata

---

## 📝 Example User Journey

### Scenario: User has billing issue

1. **Widget opens** → Language selection (Arabic selected)
2. **Root menu** → 5 flows displayed
3. **User clicks** "🚨 Technical Support & Troubleshooting"
4. **Branches** → "💳 Billing Issues" and "🔧 Technical Issues"
5. **User clicks** "💳 Billing Issues"
6. **Response** → Detailed billing issue explanation + [Back, Support Ticket]
7. **User clicks** "Support Ticket"
8. **Form opens** → Category pre-filled as "billing"
9. **User submits** → Email with category and flow context
10. **Confirmation** → "✅ Thank you! Our team will reply shortly"

### Database result:
```
chatbot_logs entry with:
- flow: "troubleshooting"
- branch: "billing_issues"
- category: "billing"
- metadata: {"flow":"troubleshooting","branch":"billing_issues","category":"billing"}
```

---

## 🐛 Troubleshooting

### Widget doesn't appear
- Check if `chatbot-widget.blade.php` is included in layout
- Verify route `chatbot.process` is registered
- Check browser console for errors

### Back button doesn't work
- Verify session is being stored correctly
- Check that flow_key is being passed in navigate action
- Look for session-related errors in browser console

### Ticket not received
- Check email configuration in `.env`
- Verify `SupportTicketMail` class exists
- Check Laravel logs for email errors: `storage/logs/laravel.log`

### Category not in email
- Verify category is being passed from Widget
- Check `SupportTicketMail` template
- Verify metadata is being stored in database

### Language not switching
- Verify browser locale is set correctly
- Check that language parameter is being sent to backend
- Verify ChatbotFlowService has both Arabic and English flows

---

## 📞 Support

For issues or questions about the chatbot system, refer to:
- Database logs in `storage/logs/laravel.log`
- Chatbot interaction logs in `chatbot_logs` table
- Browser console for frontend errors
- Email logs for delivery issues

