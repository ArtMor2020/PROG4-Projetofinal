interface FileGridProps {
  columns: number;
}

export default function FileGrid({ columns }: FileGridProps) {
  return (
    <div
      className="grid gap-4"
      style={{ gridTemplateColumns: `repeat(${columns}, minmax(0, 1fr))` }}
    >
      {Array.from({ length: 18 }).map((_, i) => (
        <div
          key={i}
          className="h-24 bg-gray-800 border rounded flex items-center justify-center text-gray-500"
          style={{ backgroundColor: '#25282A' }}
        >
          File {i + 1}
        </div>
      ))}
    </div>
  );
}
