// Helpers for rendering PreOrder status (labels come from the API already
// resolved through PreOrderStatus::label()).

// Badge background color per status label.
export const statusColor = (status) => {
    switch (status) {
        case "Requested":
            return "bg-yellow-500";
        case "Accepted Request":
            return "bg-blue-500";
        case "Prepayment Request":
            return "bg-amber-500";
        case "Confirmed Prepayment":
            return "bg-indigo-500";
        case "In Shipping":
        case "Pickup":
        case "On The Way":
            return "bg-primary";
        case "Delivered":
            return "bg-green-500";
        case "Cancelled":
        case "Refund":
            return "bg-red-500";
        default:
            return "bg-slate-400";
    }
};

// Terminal states that break the normal fulfilment flow.
export const isCancelledStatus = (status) =>
    status === "Cancelled" || status === "Refund";

// Ordered fulfilment path. Prepayment steps are only surfaced when the order
// actually went through a prepayment stage.
const BASE_FLOW = [
    "Requested",
    "Accepted Request",
    "In Shipping",
    "Pickup",
    "On The Way",
    "Delivered",
];

const PREPAY_FLOW = [
    "Requested",
    "Accepted Request",
    "Prepayment Request",
    "Confirmed Prepayment",
    "In Shipping",
    "Pickup",
    "On The Way",
    "Delivered",
];

// Returns { steps, currentIndex } for the progress tracker.
// currentIndex is the index of the current status within steps (-1 for
// cancelled / refunded / unknown, handled separately by the UI).
export const progressFor = (status) => {
    const flow = PREPAY_FLOW.includes(status) ? PREPAY_FLOW : BASE_FLOW;
    return {
        steps: flow,
        currentIndex: flow.indexOf(status),
    };
};
