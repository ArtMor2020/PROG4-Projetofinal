export const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8080";

export async function fetchFromApi(endpoint: string, options: RequestInit = {}) {
  const res = await fetch(`${API_URL}${endpoint}`, {
    headers: {
      "Content-Type": "application/json",
    },
    ...options,
  });

  if (!res.ok) {
    throw new Error(`API error: ${res.status}`);
  }
  return res.json();
}
