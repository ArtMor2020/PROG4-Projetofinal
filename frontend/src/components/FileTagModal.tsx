"use client";

import { useState } from "react";

interface Tag {
  id: number;
  name: string;
  color: string;
}

interface FileTagModalProps {
  isOpen: boolean;
  onClose: () => void;
  fileTags: Tag[];       // tags already on file
  allTags: Tag[];        // all available tags
  onSave: (selectedTags: Tag[]) => void;
}

export default function FileTagModal({ isOpen, onClose, fileTags, allTags, onSave }: FileTagModalProps) {
  const [search, setSearch] = useState("");
  const [selectedTags, setSelectedTags] = useState<Tag[]>(fileTags);
  const [columns, setColumns] = useState(2); // default columns

  if (!isOpen) return null;

  // Helper: check if a tag is selected
  const isSelected = (tag: Tag) => selectedTags.some((t) => t.id === tag.id);

  // Toggle selection
  const toggleTag = (tag: Tag) => {
    if (isSelected(tag)) {
      setSelectedTags(selectedTags.filter((t) => t.id !== tag.id));
    } else {
      setSelectedTags([...selectedTags, tag]);
    }
  };

  // Filter tags by search
  const lowerSearch = search.toLowerCase();
  const filteredFileTags = selectedTags.filter((t) => t.name.toLowerCase().includes(lowerSearch));
  const filteredOtherTags = allTags
    .filter((t) => !isSelected(t))
    .filter((t) => t.name.toLowerCase().includes(lowerSearch));

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
      <div
        className="bg-[#181A1B] rounded-lg shadow-lg w-3/4 max-w-4xl h-3/4 flex flex-col overflow-hidden"
      >
        {/* Top controls */}
        <div className="flex items-center gap-4 p-4" style={{ backgroundColor: "#202324" }}>
          <input
            type="text"
            placeholder="Search tags..."
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            className="border px-3 py-2 rounded text-white flex-1"
            style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
          />

          {/* Columns selector */}
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
            className="px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#00A140" }}
            onClick={() => onSave(selectedTags)}
          >
            Save
          </button>
          <button
            className="px-3 py-2 rounded text-white"
            style={{ backgroundColor: "#B1030C" }}
            onClick={onClose}
          >
            Cancel
          </button>
        </div>

        {/* Tags lists */}
        <div className="flex-1 flex flex-col gap-2 p-4">
          {/* Tags already on file */}
          <div className="flex-1 overflow-auto border rounded p-2" style={{ backgroundColor: "#202324" }}>
            <div className="font-semibold text-white mb-2">Tags on File</div>
            <div className="grid gap-2" style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}>
              {filteredFileTags.map((tag) => (
                <label
                  key={tag.id}
                  className="flex items-center gap-2 p-2 rounded text-white"
                  style={{ backgroundColor: tag.color }}
                >
                  <input
                    type="checkbox"
                    checked={true}
                    onChange={() => toggleTag(tag)}
                    className="accent-black"
                  />
                  <span>{tag.name}</span>
                </label>
              ))}
              {filteredFileTags.length === 0 && (
                <div className="text-gray-400 col-span-2">No tags on file</div>
              )}
            </div>
          </div>

          {/* Divider */}
          <div className="border-t border-gray-600 my-2"></div>

          {/* Tags not on file */}
          <div className="flex-1 overflow-auto border rounded p-2" style={{ backgroundColor: "#202324" }}>
            <div className="font-semibold text-white mb-2">Available Tags</div>
            <div className="grid gap-2" style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}>
              {filteredOtherTags.map((tag) => (
                <label
                  key={tag.id}
                  className="flex items-center gap-2 p-2 rounded text-white"
                  style={{ backgroundColor: tag.color }}
                >
                  <input
                    type="checkbox"
                    checked={false}
                    onChange={() => toggleTag(tag)}
                    className="accent-black"
                  />
                  <span>{tag.name}</span>
                </label>
              ))}
              {filteredOtherTags.length === 0 && (
                <div className="text-gray-400 col-span-2">No available tags</div>
              )}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
