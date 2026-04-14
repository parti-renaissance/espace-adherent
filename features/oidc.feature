@app
@renaissance_user
Feature: OpenID Connect (OIDC) flow for the dashboard-rfe client
    In order to authenticate a Cloud Run BFF against the Renaissance server
    As a confidential OIDC client
    I need to discover, fetch keys, verify userinfo, and not break legacy clients

    Scenario: Discovery endpoint exposes OIDC metadata on user_vox_host
        When I send a "GET" request to "/.well-known/openid-configuration"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "issuer" should exist
        And the JSON node "authorization_endpoint" should exist
        And the JSON node "token_endpoint" should exist
        And the JSON node "userinfo_endpoint" should exist
        And the JSON node "jwks_uri" should exist
        And the JSON node "id_token_signing_alg_values_supported[0]" should be equal to "RS256"
        And the JSON node "scopes_supported[0]" should be equal to "openid"
        And the JSON node "scopes_supported[1]" should be equal to "profile"
        And the JSON node "scopes_supported[2]" should be equal to "email"
        And the JSON node "scopes_supported[3]" should be equal to "offline_access"
        And the JSON node "end_session_endpoint" should exist

    Scenario: JWKS endpoint exposes the RSA public key
        When I send a "GET" request to "/oauth/v2/jwks"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "keys[0].kty" should be equal to "RSA"
        And the JSON node "keys[0].use" should be equal to "sig"
        And the JSON node "keys[0].alg" should be equal to "RS256"
        And the JSON node "keys[0].kid" should exist
        And the JSON node "keys[0].n" should exist
        And the JSON node "keys[0].e" should exist

    Scenario: Userinfo rejects requests without a Bearer token
        When I send a "GET" request to "/oauth/v2/userinfo"
        Then the response status code should be 401
        And the response should be in JSON

    Scenario: Userinfo with a valid OIDC Bearer token returns claims
        Given I am logged with "carl999@example.fr" via OAuth client "Dashboard RFE" with scopes "openid profile email"
        When I send a "GET" request to "/oauth/v2/userinfo"
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON node "sub" should exist
        And the JSON node "email" should be equal to "carl999@example.fr"
        And the JSON node "name" should exist
        And the JSON node "given_name" should exist
        And the JSON node "family_name" should exist

    Scenario: Userinfo rejects Bearer token lacking the openid scope
        Given I am logged with "carl999@example.fr" via OAuth client "Dashboard RFE" with scopes "profile email"
        When I send a "GET" request to "/oauth/v2/userinfo"
        Then the response status code should be 403
        And the response should be in JSON
        And the JSON node "error" should be equal to "insufficient_scope"

    Scenario: End-session without id_token_hint redirects to login form
        When I send a "GET" request to "/oauth/v2/end-session"
        Then the response status code should be 302
        And the header "Location" should match "#^/connexion$#"

    Scenario: End-session with malformed id_token_hint redirects to login form
        When I send a "GET" request to "/oauth/v2/end-session?id_token_hint=invalid&post_logout_redirect_uri=https%3A%2F%2Fattacker.example.com"
        Then the response status code should be 302
        And the header "Location" should match "#^/connexion$#"

    Scenario: Non-OIDC client (jemarche_app scope) receives session UUID in both session_id and legacy id_token fields
        When I send a "POST" request to "/oauth/v2/token" with parameters:
            | key           | value                                       |
            | client_secret | 4THZGbOfHJvRHk8bHdtZP3BTrMWFod6bOZb2mY3wLE= |
            | client_id     | 4222f4ce-f994-45f7-9ff5-f9f09ab3992b        |
            | grant_type    | password                                    |
            | scope         | jemarche_app                                |
            | username      | carl999@example.fr                          |
            | password      | secret!12345                                |
        Then the response status code should be 200
        And the response should be in JSON
        And the JSON should be equal to:
            """
            {
              "token_type": "Bearer",
              "expires_in": @integer@,
              "access_token": "@string@",
              "session_id": "@uuid@",
              "id_token": "@uuid@",
              "refresh_token": "@string@"
            }
            """
