# 🚀 High-Load Event Processor (PHP + Swoole + Redis + PostgreSQL)

### Senior PHP Showcase Project
A production-ready microservice architecture designed to handle high-frequency analytical events with guaranteed delivery, rate limiting, and efficient data persistence — featuring a **real-time monitoring dashboard**.

---

## 🏗 Architecture Overview
This project demonstrates **Event-Driven Architecture** (EDA) patterns to solve common high-load challenges:
*   **Traffic Spike Protection:** Redis acts as a high-speed buffer (Message Broker) between the API Gateway and the Database.
*   **Asynchronous Processing:** A dedicated Consumer process handles DB persistence, decoupling ingestion from storage.
*   **Reliability:** Manual Acknowledgment (ack/nack) strategy ensures zero data loss during processing.
*   **Persistence:** PostgreSQL JSONB for flexible analytical payloads with indexing for SQL performance.

---

## 🛠 Tech Stack
*   **Backend:** PHP 8.4 + **Swoole** (High-performance Coroutine-based Server)
*   **Message Broker:** **Redis** (List-based Queue)
*   **Database:** **PostgreSQL** (with JSONB support)
*   **Monitoring:** Real-time Dashboard (HTML5 + Chart.js via specialized Monitor Service)
*   **Infrastructure:** Docker, Docker Compose
*   **Load Testing:** Artillery

---

## 💎 Key Engineering Features

*   **Swoole Coroutine Connection Pooling:** Implemented custom connection pools for Redis and PostgreSQL. This eliminates TCP handshake overhead and significantly increases throughput by reusing established connections across coroutines.
*   **Non-Blocking I/O:** Leverages **Swoole Coroutines** for all network operations, allowing thousands of concurrent connections with minimal memory footprint.
*   **Zero-Loss Pipeline:** Implements a strict "Process then Ack" strategy. Messages are only removed from the Redis buffer after a successful PostgreSQL `COMMIT`.
*   **Isolated Monitoring:** A dedicated **Monitor Microservice** (running as an independent Swoole process) ensures system visibility even when the main API is under 100% CPU stress.
*   **Real-time RPS Analytics:** The dashboard calculates **Requests Per Second (RPS)** on the client-side by delta-tracking Redis counters, providing precise performance metrics during tests.
*   **Distributed Rate Limiting:** Built-in protection using Redis-based sliding windows to maintain stability across multiple API instances.

---

## 🚦 Getting Started

### Prerequisites
*   Docker & Docker Compose

### Installation & Launch
```bash
# 1. Clone the repository
git clone https://github.com/roman-shneer/high-load-event-processor-php.git
cd high-load-event-processor-php

# 2. Start the infrastructure
docker-compose up --build
```

### Access Points


| Service | URL |
|---------|-----|
| **API Gateway** | `http://127.0.0.1:8000` |
| **Real-time Dashboard** | `http://127.0.0.1:80` |
| **PostgreSQL** | `localhost:5432` (user: `user`, pass: `pass`) |

---

## 🧪 Testing & Performance

### Send a Tracking Event
```bash
curl -X POST http://127.0.0.1:8000 \
     -H "Content-Type: application/json" \
     -d '{
       "sessionId": "550e8400-e29b-41d4-a716-446655440000",
       "eventType": "product_view",
       "payload": { "productId": 123, "price": 99.99 }
     }'
```

### Run Stress Test (Artillery)
```bash
# Performance suite (RPS ramp-up)
artillery run performance/main.yml

# 1M Events insertion test
artillery run performance/insert1m.yml
```

#### artillery run performance/main.yml
<img width="813" height="759" alt="image" src="https://github.com/user-attachments/assets/17c1e1a8-a19b-4244-8bc4-3d1126504bc4" />

#### artillery run performance/insert1m.yml
<img width="837" height="751" alt="image" src="https://github.com/user-attachments/assets/bab0491f-a892-43a8-bf16-82d6be926127" />


---

## 📊 Real-Time Monitoring
The dashboard (accessible at `:80`) streams writing metrics:
*   **Events RPS Redis** — Live Events per second (blue line)
*   **Events RPS Postgres** — PostgreSQL writes per second (green line)

---

## 📄 License
MIT License
