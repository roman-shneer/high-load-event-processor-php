# High-Load Event Processor (PHP + Swoole)

High-performance asynchronous system for ingesting and processing events with zero data loss. Built using PHP, Swoole, Redis (as a buffer), and PostgreSQL.

## 🚀 Key Features

*   **Asynchronous Ingestion:** Built on Swoole HTTP server with coroutines for non-blocking I/O.
*   **Persistent Buffering:** Uses Redis for reliable queuing between the API and workers.
*   **Worker Pool:** Efficient background workers that process events and persist them to PostgreSQL.
*   **Database Connection Pooling:** Optimized SQL execution with Swoole-native connection pooling.
*   **Scalability:** Separate services for Ingestion (Server), Processing (Worker), and Monitoring.
*   **Performance Monitoring:** Real-time metrics via a dedicated monitoring microservice.

---

## 🏗 System Architecture

1.  **API Gateway (`server.php`):** Swoole HTTP server receives JSON events and pushes them to Redis.
2.  **Queue (Redis):** Acts as a high-speed buffer to handle traffic spikes.
3.  **Processors (`worker.php`):** Background consumers that read from Redis and batch-insert into DB.
4.  **Monitor (`monitor.php`):** Provides insights into queue depth and processing speed.

---

## 📂 Project Structure

```text
├── server.php        # API Gateway (Swoole HTTP Server)
├── worker.php        # Event Processor (Consumer)
├── monitor.php       # Real-time Monitoring Service
├── performance/      # Artillery load testing scenarios
├── docker-compose.yml
└── Dockerfile
```

---

## 🛠 Installation & Launch

### Prerequisites
*   Docker & Docker Compose

### Step-by-Step Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com
   cd high-load-event-processor-php
   ```

2. **Spin up the infrastructure:**
   ```bash
   docker-compose up --build -d
   ```

3. **Verify the services:**
   The system will automatically start the API, Worker, Redis, and PostgreSQL.

---

## 🔗 Access Points (Host Machine)


| Service            | Address                  | Credentials / Info          |
|--------------------|--------------------------|-----------------------------|
| **API Gateway**    | `http://127.0.0.1:8000`  | POST `/event`               |
| **Monitor**        | `http://127.0.0.1:8001`  | System Metrics              |
| **PostgreSQL**     | `localhost:5432`         | `user: user`, `pass: pass`  |
| **Redis**          | `localhost:6379`         | Event Queue Buffer          |

---

## 📈 Performance & Load Testing

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
The system is tested using **Artillery** to ensure low latency and high throughput.

### Run Stress Test (Artillery)
```bash
# Ensure you have artillery installed: npm install -g artillery
# Performance suite (RPS ramp-up)
artillery run performance/main.yml
```
<img width="813" height="759" alt="image" src="https://github.com/user-attachments/assets/17c1e1a8-a19b-4244-8bc4-3d1126504bc4" />


#### Insert 1 million events (Artillery)
```bash
# Ensure you have artillery installed: npm install -g artillery
# 1M Events insertion test
artillery run performance/insert1m.yml
```
<img width="837" height="751" alt="image" src="https://github.com/user-attachments/assets/bab0491f-a892-43a8-bf16-82d6be926127" />

**Results:**
*   **Throughput:** 10,000+ requests per second (depends on hardware).
*   **Reliability:** 0% packet loss due to asynchronous coroutine-based Redis pushing.

---
## 🛠 Technical Implementation Details

*   **Swoole Coroutines:** Used for non-blocking communication with Redis and Postgres.
*   **Connection Pooling:** Implemented to prevent "Too many connections" errors under high load.
*   **Graceful Shutdown:** Workers are designed to finish processing the current batch before stopping.


## 📊 Real-Time Monitoring
The dashboard (accessible at `:80`) streams writing metrics:
*   **Events RPS Redis** — Live Events per second (blue line)
*   **Events RPS Postgres** — PostgreSQL writes per second (green line)

---

## 📄 License
MIT License
