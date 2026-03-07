FROM bash:5.2

# タイムゾーンの設定
ENV TZ=Asia/Tokyo

# 作業ディレクトリの設定
WORKDIR /app

# テスト実行スクリプトのコピー
COPY run-tests.sh /app/run-tests.sh

# スクリプトを実行可能にする
RUN chmod +x /app/run-tests.sh
