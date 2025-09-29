"use client";

import React, { useState } from "react";
import { useRouter } from "next/navigation";

interface RegisterModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export default function RegisterModal({ isOpen, onClose }: RegisterModalProps) {
  const router = useRouter();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [confirmPassword, setConfirmPassword] = useState("");
  const [error, setError] = useState("");

  if (!isOpen) return null;

  const handleSignup = async () => {
    setError("");

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
      setError("Please enter a valid email address");
      return;
    }

    if (password !== confirmPassword) {
      setError("Passwords do not match");
      return;
    }

    try {
      // Register the user
      const register = await fetch('/api/auth/register', {
        headers: { "Content-Type": "application/json" },
        method: "POST",
        body: JSON.stringify({ email, password }),
        credentials: 'include',
      });

      if (!register.ok) {
        const errorData = await register.json();
        setError(errorData.message || "Registration failed");
        return;
      }

      // Log the user in after successful registration
      const login = await fetch('/api/auth/login', {
        headers: { "Content-Type": "application/json" },
        method: "POST",
        body: JSON.stringify({ email, password }),
        credentials: 'include',
      });

      const data = await login.json();

      if (!login.ok) {
        const errorData = await login.json();
        setError(errorData.message || "Login failed");
        return;
      }
      
      localStorage.setItem("token", data.token);
      
      //console.error(localStorage.getItem("token"));

      setEmail("");
      setPassword("");
      setConfirmPassword("");

      onClose();
      router.push("/files");
    } catch (err) {
      setError("Network error");
      console.error(err);
    }
  };

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
      <div className="rounded-xl shadow-lg p-6 w-96 relative" style={{ backgroundColor: "#1E2022" }}>
        <button
          onClick={onClose}
          className="absolute top-2 right-2 text-gray-500 hover:text-gray-800"
        >
          ✕
        </button>

        <h2 className="text-2xl font-semibold mb-4 text-center">Registrar</h2>

        {error && <p className="text-red-500 mb-2">{error}</p>}

        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-green-300"
        />
        <input
          type="password"
          placeholder="Senha"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-green-300"
        />
        <input
          type="password"
          placeholder="Confirmar Senha"
          value={confirmPassword}
          onChange={(e) => setConfirmPassword(e.target.value)}
          className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-green-300"
        />

        <button
          onClick={handleSignup}
          className="w-full text-white py-2 rounded-lg hover:bg-green-700 transition"
          style={{ backgroundColor: "#008532" }}
        >
          Registrar
        </button>
      </div>
    </div>
  );
}


