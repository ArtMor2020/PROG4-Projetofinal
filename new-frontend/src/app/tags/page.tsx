"use client";

import { useState, useEffect } from "react";
import { useRouter } from "next/navigation";

interface Tag {
  id: string;
  id_owner: string;
  name: string;
  description: string;
  color: string;
}

export default function TagsPage() {
  const router = useRouter();
  const [tags, setTags] = useState<Tag[]>([]);
  const [loading, setLoading] = useState(true);
  const [editingTag, setEditingTag] = useState<Tag | null>(null);
  const [error, setError] = useState<string | null>(null);

  const token = typeof window !== "undefined" ? localStorage.getItem("token") : null;

  useEffect(() => {
    if (!token) {
      router.push("/");
      return;
    }

    const fetchTags = async () => {
      try {
        const res = await fetch("/api/tags", {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!res.ok) throw new Error("Failed to fetch tags");
        const data: Tag[] = await res.json();
        setTags(data);
      } catch (err) {
        console.error(err);
        router.push("/");
      } finally {
        setLoading(false);
      }
    };

    fetchTags();
  }, [router, token]);

  const handleLogout = () => {
    localStorage.removeItem("token");
    router.push("/");
  };

  const handleDelete = async (id: string) => {
    if (!confirm("Are you sure you want to delete this tag?")) return;
    try {
      const res = await fetch(`/api/tags/${id}`, {
        method: "DELETE",
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Failed to delete tag");
      setTags(tags.filter((t) => t.id !== id));
    } catch (err) {
      console.error(err);
      setError("Failed to delete tag");
    }
  };

  const handleUpdate = async () => {
    if (!editingTag) return;
    try {
      const res = await fetch(`/api/tags/${editingTag.id}`, {
        method: "PUT",
        headers: {
          Authorization: `Bearer ${token}`,
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          name: editingTag.name,
          description: editingTag.description,
          color: editingTag.color,
        }),
      });
      if (!res.ok) throw new Error("Failed to update tag");

      setTags(tags.map((t) => (t.id === editingTag.id ? editingTag : t)));
      setEditingTag(null);
    } catch (err) {
      console.error(err);
      setError("Failed to update tag");
    }
  };

  const [searchQuery, setSearchQuery] = useState("");

  const handleSearch = async (query: string) => {
    if (!token) return;

    // if input is empty, fetch all tags
    if (!query.trim()) {
      try {
        const res = await fetch("/api/tags", {
          headers: { Authorization: `Bearer ${token}` },
        });
        if (!res.ok) throw new Error("Failed to fetch tags");
        const data: Tag[] = await res.json();
        setTags(data);
        setError(null);
      } catch (err) {
        console.error(err);
        setError("Failed to fetch tags");
      }
      return;
    }

    // otherwise do search
    try {
      const res = await fetch(`/api/tags/search/${encodeURIComponent(query)}`, {
        headers: { Authorization: `Bearer ${token}` },
      });
      if (!res.ok) throw new Error("Failed to search tags");
      const data: { tag: Tag; matchPercentage: number }[] = await res.json();
      setTags(data.map((d) => d.tag));
      setError(null);
    } catch (err) {
      console.error(err);
      setError("Search failed");
    }
  };


  if (loading) return <div className="flex items-center justify-center h-screen text-white">Carregando...</div>;

  return (
    <div className="flex flex-col h-screen bg-gray-900 text-white">
      {/* Top bar */}
      <div className="w-full flex flex-col md:flex-row items-center justify-between p-4 mb-6 bg-gray-800 shadow-md rounded-b-lg">
        <h1 className="text-3xl font-bold text-white mb-2 md:mb-0">TagScribe!</h1>
        <div className="flex flex-wrap items-center gap-4">
        {/* Search input */}
        <input
          type="text"
          placeholder="Pesquisar tags..."
          value={searchQuery}
          onChange={(e) => {
            setSearchQuery(e.target.value);
            handleSearch(e.target.value);
          }}
          className="px-3 py-2 rounded-lg bg-gray-700 text-white focus:outline-none focus:ring focus:ring-yellow-500"
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

          <button
            onClick={handleLogout}
            className="px-4 py-2 text-white rounded-lg shadow hover:bg-red-600 transition"
            style={{ backgroundColor: "#BF0000" }}
          >
            Sair
          </button>
        </div>
      </div>

      {/* Error */}
      {error && <p className="text-red-500 text-center mt-2">{error}</p>}

      {/* Tags list */}
      <div className="p-4 overflow-auto flex-1">
        {tags.map((tag) => (
            <div
                key={tag.id}
                className="flex items-center justify-between bg-gray-700 p-3 mb-2 rounded shadow"
                >
                {/* Tag info in a row */}
                <div className="flex items-center gap-4"
                >
                <div
                    className="w-6 h-6 rounded border border-gray-500"
                    style={{ backgroundColor: tag.color }}
                />
                <p className="font-semibold">{tag.name}</p>
                <p className="text-gray-300">{tag.description}</p>
                </div>

                {/* Edit/Delete buttons */}
                
                <div className="flex gap-2">
                <button onClick={() => router.push(`/tags/${tag.id}`)}
                    className="px-3 py-1 bg-yellow-600 rounded hover:bg-blue-700 transition"
                >
                    Ver Arquivos
                </button>
                <button
                    onClick={() => setEditingTag({ ...tag })}
                    className="px-3 py-1 bg-blue-600 rounded hover:bg-blue-700 transition"
                >
                    Editar
                </button>
                <button
                    onClick={() => handleDelete(tag.id)}
                    className="px-3 py-1 bg-red-600 rounded hover:bg-red-700 transition"
                >
                    Apagar
                </button>
                </div>
            </div>
            ))}
      </div>

      {/* Edit modal */}
      {editingTag && (
        <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
          <div className="bg-gray-800 rounded-xl p-6 w-96">
            <h2 className="text-2xl font-semibold mb-4 text-center">Editar Tag</h2>

            <input
              type="text"
              placeholder="Nome"
              value={editingTag.name}
              onChange={(e) => setEditingTag({ ...editingTag, name: e.target.value })}
              className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300 bg-gray-700 text-white"
            />
            <input
              type="text"
              placeholder="Descrição"
              value={editingTag.description}
              onChange={(e) => setEditingTag({ ...editingTag, description: e.target.value })}
              className="w-full px-3 py-2 mb-3 border rounded-lg focus:outline-none focus:ring focus:ring-blue-300 bg-gray-700 text-white"
            />
            <input
              type="color"
              value={editingTag.color}
              onChange={(e) => setEditingTag({ ...editingTag, color: e.target.value })}
              className="w-full mb-4 h-10 p-0 rounded border border-gray-500"
            />

            <div className="flex justify-between">
              <button
                onClick={() => setEditingTag(null)}
                className="px-4 py-2 bg-gray-600 rounded hover:bg-gray-700 transition"
              >
                Cancelar
              </button>
              <button
                onClick={handleUpdate}
                className="px-4 py-2 bg-green-600 rounded hover:bg-green-700 transition"
              >
                Salvar
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
