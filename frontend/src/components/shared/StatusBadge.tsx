import { Badge } from "@ui/badge";
import { FC } from "react";

interface Props {
  status: string;
}
export const StatusBadge: FC<Props> = ({ status }) => {
  const STATUSES = {
    completed: {
      label: "Завершено",
      variant: "green",
    },
    in_progress: {
      label: "В работе",
      variant: "blue",
    },
  };

  return (
    <Badge
      variant={STATUSES[status as string].variant}
      className="py-2"
    >
      {STATUSES[status].label}
    </Badge>
  );
};
