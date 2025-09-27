"use client";

interface AddTagModalProps {
  isOpen: boolean;
  onClose: () => void;
  onAdd: (tag: { name: string; color: string; description: string }) => void;
}

export default function AddTagModal({ isOpen, onClose, onAdd }: AddTagModalProps) {
  if (!isOpen) return null;

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const form = e.currentTarget;
    const data = new FormData(form);

    const tag = {
      name: data.get("name") as string,
      color: data.get("color") as string,
      description: data.get("description") as string,
    };

    onAdd(tag);
    onClose();
  };

  return (
    <div className="fixed inset-0 flex items-center justify-center bg-black bg-opacity-70 z-50">
      <form
        onSubmit={handleSubmit}
        className="rounded-lg shadow-lg w-1/3 p-6 flex flex-col gap-4 text-white"
        style={{ backgroundColor: "#181A1B" }}
      >
        <h2 className="text-xl font-semibold">Add Tag</h2>

        {/* Name */}
        <input
          name="name"
          type="text"
          placeholder="Tag name"
          required
          className="border px-3 py-2 rounded text-white"
          style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
        />

        {/* Color */}
        <input
          name="color"
          type="text"
          placeholder="Color (e.g., #FF0000)"
          required
          className="border px-3 py-2 rounded text-white"
          style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
        />

        {/* Description */}
        <textarea
          name="description"
          placeholder="Description"
          rows={3}
          className="border px-3 py-2 rounded text-white resize-none"
          style={{ backgroundColor: "#25282A", borderColor: "#3A3C3D" }}
        ></textarea>

        {/* Actions */}
        <div className="flex justify-end gap-3 mt-2">
          <button
            type="button"
            className="px-4 py-2 rounded text-white"
            style={{ backgroundColor: "#B1030C" }}
            onClick={onClose}
          >
            Cancel
          </button>
          <button
            type="submit"
            className="px-4 py-2 rounded text-white"
            style={{ backgroundColor: "#0047B2" }}
          >
            Add Tag
          </button>
        </div>
      </form>
    </div>
  );
}
