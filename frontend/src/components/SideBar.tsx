"use client";

import { useState } from "react";
import AddTagModal from "./AddTagModal";
import AllTagsModal from "./AllTagsModal";

interface Tag {
  name: string;
  color: string;
  description: string;
}

export default function SideBar() {
  const [isAddTagOpen, setIsAddTagOpen] = useState(false);
  const [isAllTagsOpen, setIsAllTagsOpen] = useState(false);

  const [tags, setTags] = useState<Tag[]>([
    { name: "Tag 1", color: "#FF0000", description: "First tag" },
    { name: "Tag 2", color: "#00FF00", description: "Second tag" },
    { name: "Tag 3", color: "#0000FF", description: "Third tag" },
    { name: "Tag 4", color: "#FFAA00", description: "Fourth tag" },
    { name: "Tag 5", color: "#AA00FF", description: "Fifth tag" },
    { name: "Tag 6", color: "#00AAAA", description: "Sixth tag" },
    { name: "Tag 7", color: "#FF55AA", description: "Seventh tag" },
    { name: "Tag 8", color: "#55FFAA", description: "Eighth tag" },
    { name: "Tag 9", color: "#AAFF55", description: "Ninth tag" },
    { name: "Tag 10", color: "#FF5555", description: "Tenth tag" },
    { name: "Tag 11", color: "#55AAFF", description: "Eleventh tag" },
  ]);

  const handleAddTag = (tag: Tag) => {
    setTags((prev) => [...prev, tag]);
  };

  return (
    <>
      <div
        className="w-60 p-4 flex flex-col h-full"
        style={{ backgroundColor: "#181A1B" }}
      >
        {/* Search tags */}
        <input
          type="text"
          placeholder="Search tags..."
          className="border px-2 py-1 rounded mb-2"
        />

        {/* Buttons */}
        <button
          onClick={() => setIsAllTagsOpen(true)}
          className="mb-2 px-3 py-1 rounded"
          style={{ backgroundColor: "#25282A" }}
        >
          All Tags
        </button>

        <button
          onClick={() => setIsAddTagOpen(true)}
          className="mb-4 px-3 py-1 text-white rounded"
          style={{ backgroundColor: "#00A140" }}
        >
          Add Tag
        </button>

        {/* Scrollable tags list */}
        <div className="flex-1 overflow-auto grid gap-2" style={{ gridTemplateColumns: "1fr" }}>
          {tags.map((tag, i) => (
            <div
              key={i}
              className="p-2 rounded text-white"
              style={{ backgroundColor: tag.color || "#1E2022" }}
            >
              <div className="font-semibold">{tag.name}</div>
              <div className="text-sm opacity-80">{tag.description}</div>
            </div>
          ))}
        </div>
      </div>

      {/* Add Tag Modal */}
      <AddTagModal
        isOpen={isAddTagOpen}
        onClose={() => setIsAddTagOpen(false)}
        onAdd={handleAddTag}
      />

      {/* All Tags Modal */}
      <AllTagsModal
        isOpen={isAllTagsOpen}
        onClose={() => setIsAllTagsOpen(false)}
        tags={tags}
      />
    </>
  );
}
