"use client";

import { useState } from "react";
import TopBar from "@/components/TopBar";
import SideBar from "@/components/SideBar";
import FileGrid from "@/components/FileGrid";

export default function WorkspacePage() {
  const [columns, setColumns] = useState(6); // default 6 columns

  return (
    <div className="flex h-screen">
      <SideBar />

      <div className="flex flex-col flex-1">
        <TopBar columns={columns} setColumns={setColumns} />

        <div className="flex-1 bg-gray-50 p-4 overflow-auto" style={{ backgroundColor: '#1B1D1E'}}>
          <FileGrid columns={columns} />
        </div>
      </div>
    </div>
  );
}
