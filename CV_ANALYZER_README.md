# Enhanced CV Analyzer Module

This document provides information about the enhanced CV Analyzer module with improved JSON response handling.

## Overview

The CV Analyzer module has been enhanced to ensure consistent JSON responses from AI providers, making it easier to parse and display structured analysis results. The improvements include:

1. Automatic JSON formatting instructions added to all CV analysis requests
2. Enhanced JSON parsing in the AIService to better handle various response formats
3. Validation and normalization of JSON responses to ensure consistent data structure

## How It Works

The system now automatically appends JSON formatting instructions to all CV analysis requests, regardless of which AI provider or model is used. This ensures that:

1. AI responses are consistently formatted as JSON
2. The JSON structure follows a standardized format
3. No extraneous text or markdown is included in the response

No additional setup is required - the enhanced JSON handling is built into the AIService.

## Features

The enhanced CV Analyzer provides the following structured data for each candidate:

- **Match Score**: A numeric score between 0.0 and 1.0 indicating overall match quality
- **Justification**: A text explanation for the score
- **Requirement Breakdown**: Detailed analysis of each job requirement with:
  - Match status (true/false)
  - Evidence from the resume
  - Individual requirement score
- **Red Flags**: Potential concerns or mismatches identified in the resume
- **Interview Questions**: Suggested questions based on the candidate's background

## Usage

### Analyzing a Single Candidate

When viewing a candidate, click the "Analyze" button to run the analysis using the default settings, or select specific AI settings and the "Enhanced JSON CV Analyzer" prompt.

### Batch Analysis

From the project candidates list, use the "Analyze All Candidates" feature with the following settings:
1. Select your preferred AI provider
2. Choose an appropriate model
3. Select "Enhanced JSON CV Analyzer" from the prompt dropdown

### Viewing Results

The analysis results are displayed on the candidate's profile page with:
- Overall match score
- Detailed breakdown of each requirement
- Red flags and concerns
- Suggested interview questions

## Troubleshooting

If you encounter issues with the CV Analyzer:

1. Check the Laravel logs for detailed error messages
2. Ensure your AI provider API keys are valid and have sufficient credits
3. Verify that the resume text was properly extracted from the uploaded document
4. Make sure project requirements are defined before analyzing candidates

## Technical Details

The enhanced CV Analyzer uses a structured prompt that explicitly instructs the AI to:
- Respond ONLY with valid JSON
- Not include any text outside the JSON object
- Not use markdown code blocks
- Include all required fields in the response

This ensures consistent parsing and display of analysis results across different AI providers and models.