"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";

interface FileModel {
  id: string;
  id_owner: string;
  name: string;
  type: string;
  path: string;
  is_deleted: string;
  content: string; // base64 encoded
}

export default function FilesPage() {
  const router = useRouter();
  const [files, setFiles] = useState<FileModel[]>([]);
  const [loading, setLoading] = useState(true);
  const [columns, setColumns] = useState(4);
  const [search, setSearch] = useState("");

  const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;

  useEffect(() => {
    if (!token) {
      router.push("/");
      return;
    }

    const fetchFiles = async () => {
      try {
        const res = await fetch("/api/file/content", {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!res.ok) throw new Error("Failed to fetch files");
        const fileModels: FileModel[] = await res.json();
        setFiles(fileModels);
      } catch (err) {
        console.error(err);
        router.push("/");
      } finally {
        setLoading(false);
      }
    };

    fetchFiles();
  }, [router, token]);

  const handleLogout = () => {
    localStorage.removeItem("token");
    router.push("/");
  };

  const handleSearch = async (query: string) => {
    if (!token) return;

    setSearch(query);

    // empty query: fetch all files
    if (!query.trim()) {
      try {
        const res = await fetch("/api/file/content", {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!res.ok) throw new Error("Failed to fetch files");
        const data: FileModel[] = await res.json();
        setFiles(data);
      } catch (err) {
        console.error(err);
      }
      return;
    }

    // search
    try {
      const res = await fetch(`/api/files/search/${encodeURIComponent(query)}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Search failed");
      const data: FileModel[] = await res.json();
      setFiles(data);
    } catch (err) {
      console.error(err);
    }
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-screen text-white" style={{ backgroundColor: "#1E2022" }}>
        Carregando...
      </div>
    );
  }

  return (
    <div className="flex flex-col items-center min-h-screen relative" style={{ backgroundColor: "#1E2022" }}>
      {/* Top bar */}
      <div className="w-full flex flex-col md:flex-row items-center justify-between p-4 mb-6 bg-gray-800 shadow-md rounded-b-lg">
        <h1 className="text-3xl font-bold text-white mb-2 md:mb-0">TagScribe!</h1>
        <div className="flex flex-wrap items-center gap-4">
          {/* Search input */}
          <input
            type="text"
            placeholder="Pesquisar arquivos..."
            value={search}
            onChange={(e) => handleSearch(e.target.value)}
            className="px-4 py-2 rounded-lg shadow bg-gray-700 text-white focus:outline-none focus:ring focus:ring-yellow-500"
          />

          <button
            onClick={() => router.push("/files")}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-blue-700 transition"
            style={{ backgroundColor: "#023DBF" }}
          >
            Arquivos
          </button>

          <button
            onClick={() => router.push("/tags")}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-green-700 transition"
            style={{ backgroundColor: "#008532" }}
          >
            Tags
          </button>

          <button
            onClick={() => router.push("/files/upload")}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-purple-700 transition"
            style={{ backgroundColor: "#6A0DAD" }}
          >
            Adicionar Arquivo
          </button>

          <div className="flex items-center gap-2">
            <label htmlFor="columns" className="text-white font-medium">Colunas:</label>
            <select
              id="columns"
              value={columns}
              onChange={(e) => setColumns(Number(e.target.value))}
              className="bg-gray-700 text-white rounded px-2 py-1"
            >
              {[2, 3, 4, 5, 6, 7, 8].map((num) => (
                <option key={num} value={num}>{num}</option>
              ))}
            </select>
          </div>

          <button
            onClick={handleLogout}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-red-600 transition"
            style={{ backgroundColor: "#BF0000" }}
          >
            Sair
          </button>
        </div>
      </div>

      {/* File grid */}
      <div
        className={`w-full max-w-8xl p-4 grid gap-6 overflow-auto`}
        style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
      >
        {files.map((file) => {
          if (!file.content) return null;

          const handleClick = () => router.push(`/files/${file.id}`);

          if (file.type === "IMAGE") {
            return (
              <div
                key={file.id}
                className="bg-gray-700 p-2 rounded-lg shadow hover:scale-105 transform transition cursor-pointer"
                onClick={handleClick}
              >
                <img
                  src={`data:image/jpeg;base64,${file.content}`}
                  alt={file.name}
                  className="w-full aspect-4/3 object-cover rounded"
                />
                <p className="mt-2 text-center font-medium text-white">{file.name}</p>
              </div>
            );
          }

          if (file.type === "VIDEO") {
            return (
              <div
                key={file.id}
                className="bg-gray-700 p-2 rounded-lg shadow hover:scale-105 transform transition cursor-pointer"
                onClick={handleClick}
              >
                <video controls className="w-full aspect-4/3 rounded">
                  <source src={`data:video/mp4;base64,${file.content}`} type="video/mp4" />
                </video>
                <p className="mt-2 text-center font-medium text-white">{file.name}</p>
              </div>
            );
          }

          // DOC / OTHER
          return (
            <div
              key={file.id}
              className="bg-gray-700 p-4 rounded-lg shadow text-center hover:scale-105 transform transition cursor-pointer"
              onClick={handleClick}
            >
              <p className="font-semibold text-white">{file.name}</p>
              <p className="text-sm text-gray-300">{file.type}</p>
            </div>
          );
        })}
      </div>
    </div>
  );
}
