# Button Clicked Pandit App – Backend API

This repository contains backend APIs for the Pandit Booking application.
The APIs are built using **PHP + MongoDB** and deployed on **Vercel**.

**Base URL:**
```
https://button-clicked-pandit-app.vercel.app
```

---

## 📌 Tech Stack
- PHP
- MongoDB Atlas
- Vercel (Serverless)
- JSON-based REST APIs

---

## 📌 Authentication
❌ No authentication required (for now)  
Frontend needs to send correct data only.

---

## 📌 API Endpoints Overview

### 1️⃣ Create Booking
**URL:**
```
POST /api/bookings/booking_create.php
```

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
  "user_id": "444",
  "pandit_id": "101",
  "pooja_type": "Griha Pravesh",
  "date": "2026-01-10",
  "time": "10:00 AM",
  "address": "Indore",
  "additional_notes": "Morning pooja",
  "payment_status": "unpaid"
}
```

**Success Response:**
```json
{
  "success": true,
  "message": "Booking created successfully",
  "data": {
    "booking_id": "65a12...",
    "booking_status": "pending",
    "payment_status": "unpaid"
  }
}
```

⚠️ **Note:** Duplicate booking (same user + pandit + date + time) is not allowed.

---

### 2️⃣ Get Booking List (User Wise)
**URL:**
```
GET /api/bookings/booking_list.php?user_id=444
```

**Success Response:**
```json
{
  "success": true,
  "data": [
    {
      "booking_id": "65a12...",
      "pooja_type": "Griha Pravesh",
      "booking_status": "pending",
      "payment_status": "unpaid",
      "created_at": "2026-01-05 12:30:00"
    }
  ]
}
```

---

### 3️⃣ Update Booking Status (Pandit Side)
**URL:**
```
PUT /api/bookings/update_status.php
```

**Body (JSON):**
```json
{
  "booking_id": "65a12...",
  "status": "accepted"
}
```

**Allowed status values:**
- `accepted`
- `rejected`

---

### 4️⃣ Get Pandit Price
**URL:**
```
GET /api/prices/price.php?pooja_type=Griha%20Pravesh&travel_preference=Within%20State
```

**Success Response:**
```json
{
  "success": true,
  "data": {
    "base_price": 2500,
    "prices": {
      "junior": 3750,
      "mid": 4875,
      "senior": 6000
    }
  }
}
```

---

## 📌 Booking Status Flow
```
pending → accepted / rejected
```

---

## 📌 Payment Status Values
```
unpaid | paid | pending
```