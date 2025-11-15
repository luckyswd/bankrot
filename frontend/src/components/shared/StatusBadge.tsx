import { Badge } from "@ui/badge";
import { FC } from "react";

interface Props {
  status: string;
}
export const StatusBadge: FC<Props> = ({ status }) => {
  const STATUSES: Record<string, { label: string; variant: string }> = {
    completed: {
      label: "Завершено",
      variant: "green",
    },
    'Завершено': {
      label: "Завершено",
      variant: "green",
    },
    in_progress: {
      label: "В работе",
      variant: "blue",
    },
    'В работе': {
      label: "В работе",
      variant: "blue",
    },
  };

  const statusConfig = STATUSES[status] || { label: status, variant: "secondary" };

  return (
    <Badge
      variant={statusConfig.variant as "default" | "secondary" | "destructive" | "outline"}
      className="py-1 text-nowrap"
    >
      {statusConfig.label}
    </Badge>
  );
};
