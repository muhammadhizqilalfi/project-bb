FROM python:3.10-alpine

# Install Docker CLI agar container bot bisa menjalankan perintah docker exec & docker restart
RUN apk add --no-cache docker-cli

WORKDIR /app

# Install library python-telegram-bot
RUN pip install --no-cache-dir python-telegram-bot

# Copy script Python
COPY bot.py .

CMD ["python", "bot.py"]