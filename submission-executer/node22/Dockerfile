FROM node:22

# typescript のセットアップ
RUN npm install -g npm typescript ts-node @types/node tsx

# 作業ディレクトリの設定
WORKDIR /app

# Copy the run-tests.sh script
COPY ./ /app

# Make the script executable
RUN chmod +x /app/run-tests.sh

CMD ["/app/run-tests.sh"]
