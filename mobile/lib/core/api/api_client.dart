import 'dart:convert';

import 'package:http/http.dart' as http;

/// Client HTTP minimal vers l'API Laravel. `baseUrl` pointe vers
/// farm-erp.trugroup.cm en production ; configurable pour les environnements
/// de dev/démo (cf. README.md racine du dépôt).
class ApiClient {
  ApiClient({required this.baseUrl, this.token});

  final String baseUrl;
  String? token;

  Map<String, String> get _headers => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (token != null) 'Authorization': 'Bearer $token',
  };

  Future<http.Response> get(String chemin) {
    return http.get(Uri.parse('$baseUrl$chemin'), headers: _headers);
  }

  Future<http.Response> post(String chemin, Map<String, dynamic> corps) {
    return http.post(
      Uri.parse('$baseUrl$chemin'),
      headers: _headers,
      body: jsonEncode(corps),
    );
  }
}
