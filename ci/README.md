# API Specific CI/CD Approach
This API complies with the `common-ci` approach.

The following secrets are required:
* `DEVICE_DETECTION_KEY` - [license key](https://51degrees.com/pricing) for downloading assets (TAC hashes file and TAC CSV data file)
    * Example: `V3RYL0NGR4ND0M57R1NG`

The following secrets are optional:
* `DEVICE_DETECTION_URL` - URL for downloading the enterprise TAC hashes file
    * Default: `https://distributor.51degrees.com/api/v2/download?LicenseKeys=DEVICE_DETECTION_KEY&Type=HashV41&Download=True&Product=V4TAC`
