"use client";

interface FileGridProps {
  columns: number;
  onFileClick: (file: { id: number; name: string; type: string; preview?: string }) => void;
}

export default function FileGrid({ columns, onFileClick }: FileGridProps) {
  const files = Array.from({ length: 18 }).map((_, i) => ({
    id: i,
    name: `File ${i + 1}`,
    type: "image",
    preview: "", // later you can hook real preview URLs
  }));

  return (
    <div
      className="grid gap-4"
      style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
    >
      {files.map((file) => (
        <div
          key={file.id}
          onClick={() => onFileClick(file)}
          className="h-24 border rounded flex items-center justify-center text-gray-400 cursor-pointer hover:bg-gray-700"
          style={{ backgroundColor: "#25282A" }}
        >
          {file.name}
        </div>
      ))}
    </div>
  );
}
