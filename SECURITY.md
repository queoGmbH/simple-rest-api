# Security Policy

## Supported Versions

| Version | Supported |
|---|---|
| 0.3.x | Security fixes |
| 0.2.x | End of life |
| 0.1.x | End of life |

Once 1.0.0 is released, the two most recent minor versions will receive security fixes.

## Reporting a Vulnerability

Please **do not** open a public GitLab or GitHub issue for security vulnerabilities.

Report vulnerabilities privately by email to:

**s.hofer@queo-group.com**

Include in your report:
- A description of the vulnerability
- Steps to reproduce
- Affected versions
- Any suggested fix (optional)

You can expect an acknowledgement within **5 business days** and a resolution or status update within **30 days**.

## Security Scope

This extension handles routing and URL parameter mapping only. The following are explicitly **out of scope** and are the responsibility of the consumer:

- Authentication and authorization
- Rate limiting
- Input validation beyond scalar type coercion
- CSRF protection
- CORS configuration

See the `SECURITY NOTICE` in `Classes/Attribute/AsApiEndpoint.php` for details.

## Disclosure Policy

Once a fix is available, vulnerabilities are disclosed in the changelog under the `[SECURITY]` prefix with the release tag. The reporter is credited unless they prefer to remain anonymous.
