#!/usr/bin/env bash

# Arguments
TEST_CASES_DIR=$2
CODE_FILE=$1

# Validate arguments
if [[ -z "$TEST_CASES_DIR" || -z "$CODE_FILE" ]]; then
    echo "Usage: $0 <test_cases_dir> <code_file>"
    exit 1
fi

# CODE_FILE の改行を統一
sed -i 's/\r$//' "$CODE_FILE"

# Iterate over input files and check outputs
for input_file in "$TEST_CASES_DIR"/input*.txt; do
    index=$(basename "$input_file" | grep -o '[0-9]\+')
    echo "Running test case $index"
    expected_file="$TEST_CASES_DIR/expect${index}.txt"

    if [[ ! -f "$expected_file" ]]; then
        ls "$TEST_CASES_DIR"
        echo "Expected file $expected_file does not exist."
        exit 1
    fi

    # Execute the code with the input and capture the output
    actual_output="$TEST_CASES_DIR/output${index}.txt"
    bash "$CODE_FILE" < "$input_file" > "$actual_output"
    # 改行コードを統一
    sed -i 's/\r$//' "$actual_output"
    sed -i 's/\r$//' "$expected_file"

    # $actual_output $expected_output の中身を表示
    echo "expected_output: "
    cat "$expected_file"
    echo "actual_output: "
    cat "$actual_output"
    
    if ! cmp -s "$expected_file" "$actual_output"; then
        echo "Test case $index failed."
        exit 1
    fi
done
echo 'success'

exit 0
