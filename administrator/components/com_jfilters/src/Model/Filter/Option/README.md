### URLs
The url components, presented by the app should follow these guidelines:
1. Non ASCII characters should be encoded to UTF-8.
2. Reserved characters, should be percent encoded ([RFC 3986](https://tools.ietf.org/html/rfc3986#section-2.4) ,[RFC2396](https://www.ietf.org/rfc/rfc2396.txt)).
> Apache needs [specific configuration](http://httpd.apache.org/docs/2.2/en/mod/core.html#allowencodedslashes) to accept percent encoded forward slashes (/). Otherwise returns a `URL Not Found` error.
3. _Sub-delims_ can be used as delimiters inside a uri component ([RFC 3986 Section 2.2](https://tools.ietf.org/html/rfc3986#section-2.2)).
