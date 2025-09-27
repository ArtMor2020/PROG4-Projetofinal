"use client";

import { useState } from "react";

interface Tag {
  name: string;
  color: string;
  description: string;
}

interface AllTagsModalProps {
  isOpen: boolean;
  onClose: () => void;
  tags: Tag[];
}

export default function AllTagsModal({ isOpen, onClose, tags }: AllTagsModalProps) {
  const [search, setSearch] = useState("");
  const [sortBy, setSortBy] = useState("name");
  const [columns, setColumns] = useState(3); // default columns

  if (!isOpen) return null;

    // Filter + sort
    const filteredTags = tags
    .filter((tag) => tag.name.toLowerCase().includes(search.toLowerCase()))
    .slice() // shallow copy to avoid mutating original array
    .sort((a, b) =>
        sortBy === "name"
        ? a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: "base" })
        : 0 // newest first
    );

    if (sortBy === "newest") {
    filteredTags.reverse(); // newest tags first
    }

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
      <div
        className="rounded-lg shadow-lg w-3/4 max-w-5xl h-3/4 flex flex-col overflow-hidden"
        style={{ backgroundColor: "#181A1B" }}
      >
        {/* Top controls */}
        <div className="flex items-center gap-4 p-4" style={{ backgroundColor: "#202324" }}>
          <input
            type="text"
            placeholder="Search tags..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="border px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          />

          <select
          value={sortBy}
          onChange={(e) => setSortBy(e.target.value)}
          className="border px-3 py-2 rounded text-white"
          style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          >
          <option value="name">Sort by Name</option>
          <option value="newest">Sort by Newest</option>
          </select>

          {/* Columns dropdown */}
          <select
            value={columns}
            onChange={(e) => setColumns(Number(e.target.value))}
            className="border px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          >
            <option value={1}>1 column</option>
            <option value={2}>2 columns</option>
            <option value={3}>3 columns</option>
            <option value={4}>4 columns</option>
            <option value={5}>5 columns</option>
          </select>

          <button
            className="ml-auto px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#B1030C" }}
            onClick={onClose}
          >
            Close
          </button>
        </div>

        {/* Scrollable tag grid */}
        <div
          className="flex-1 overflow-auto p-4 grid gap-4"
          style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
        >
          {filteredTags.map((tag, i) => (
            <div
              key={i}
              className="p-4 rounded text-white flex flex-col justify-center"
              style={{ backgroundColor: tag.color || "#1E2022" }}
            >
              <div className="font-semibold">{tag.name}</div>
              <div className="text-sm opacity-80">{tag.description}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
